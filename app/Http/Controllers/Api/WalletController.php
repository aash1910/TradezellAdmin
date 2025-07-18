<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;
use App\Models\Package;
use App\Models\Order;
use App\User;
use Stripe\Stripe;
use Stripe\Account;
use Stripe\Transfer;
use Stripe\Payout;
use Stripe\Exception\ApiErrorException;
use Carbon\Carbon;

class WalletController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Get wallet balance for the authenticated user
     */
    public function getBalance()
    {
        try {
            $user = Auth::user();
            
            // Calculate available balance (completed payments - withdrawals)
            $completedPayments = Payment::where('user_id', $user->id)
                ->where('payment_type', 'release')
                ->where('status', 'succeeded')
                ->sum('amount');

            $withdrawals = Payment::where('user_id', $user->id)
                ->where('payment_type', 'withdrawal')
                ->where('status', 'succeeded')
                ->sum('amount');

            $availableBalance = $completedPayments - abs($withdrawals);

            // Calculate pending balance (payments that will be released when delivery is completed)
            $pendingPayments = Payment::where('user_id', $user->id)
                ->where('payment_type', 'release')
                ->where('status', 'pending')
                ->sum('amount');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'available_balance' => max(0, $availableBalance),
                    'pending_balance' => max(0, $pendingPayments),
                    'total_balance' => max(0, $availableBalance + $pendingPayments),
                    'currency' => config('services.currency'),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Wallet balance error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get wallet balance'
            ], 500);
        }
    }

    /**
     * Get wallet transactions
     */
    public function getTransactions(Request $request)
    {
        try {
            $user = Auth::user();
            $page = $request->get('page', 1);
            $limit = min($request->get('limit', 20), 100);

            $transactions = Payment::where('user_id', $user->id)
                ->whereIn('payment_type', ['release', 'withdrawal', 'refund'])
                ->with(['package'])
                ->orderBy('created_at', 'desc')
                ->paginate($limit);

            $formattedTransactions = $transactions->getCollection()->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'type' => $payment->payment_type,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'description' => $this->getTransactionDescription($payment),
                    'created_at' => $payment->created_at->toISOString(),
                    'package_id' => $payment->package_id,
                    'order_id' => $payment->package?->order?->id,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $formattedTransactions,
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'total_pages' => $transactions->lastPage(),
                    'total_items' => $transactions->total(),
                    'per_page' => $transactions->perPage(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Wallet transactions error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get wallet transactions'
            ], 500);
        }
    }

    /**
     * Get pending payments (payments that will be released when sender completes delivery)
     */
    public function getPendingPayments()
    {
        try {
            $user = Auth::user();
            
            $pendingPayments = Payment::where('user_id', $user->id)
                ->where('payment_type', 'release')
                ->where('status', 'pending')
                ->with(['package'])
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedPayments = $pendingPayments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'type' => 'payment',
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'description' => "Payment for package #{$payment->package_id} - {$payment->package->pickup_address} to {$payment->package->drop_address}",
                    'created_at' => $payment->created_at->toISOString(),
                    'package_id' => $payment->package_id,
                    'order_id' => $payment->package?->order?->id,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $formattedPayments
            ]);

        } catch (\Exception $e) {
            Log::error('Pending payments error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get pending payments'
            ], 500);
        }
    }

    /**
     * Request withdrawal
     */
    public function requestWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100', // Minimum SEK1.00 in cents
            'currency' => 'nullable|string|size:3',
        ]);

        try {
            $user = Auth::user();
            $amount = $request->amount / 100; // Convert from cents to dollars
            $currency = $request->currency ?: config('services.currency');

            // Check available balance
            $availableBalance = $this->getAvailableBalance($user);
            if ($amount > $availableBalance) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Insufficient available balance'
                ], 400);
            }

            // Check minimum withdrawal amount
            $minimumAmount = 1; // 1kn minimum
            if ($amount < $minimumAmount) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Minimum withdrawal amount is \${$minimumAmount}"
                ], 400);
            }

            // Check main Stripe account balance
            $mainAccountBalance = $this->getMainAccountBalance($currency);
            if ($mainAccountBalance < $request->amount) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Insufficient funds in main account to process withdrawal'
                ], 400);
            }

            // Get or create Stripe Connect account
            $stripeAccount = $this->getOrCreateStripeAccount($user);

            // Check if account has external accounts for the requested currency
            $externalAccounts = \Stripe\Account::allExternalAccounts(
                $stripeAccount->id,
                ['object' => 'bank_account', 'limit' => 100]
            );

            $hasExternalAccountForCurrency = false;
            foreach ($externalAccounts->data as $externalAccount) {
                if ($externalAccount->currency === strtolower($currency)) {
                    $hasExternalAccountForCurrency = true;
                    break;
                }
            }

            if (!$hasExternalAccountForCurrency) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Withdrawal failed: Sorry, you don't have any external accounts in that currency ({$currency}). Please add a bank account or debit card in your Stripe Connect dashboard first."
                ], 500);
            }

            // Create transfer to Stripe Connect account
            $transfer = Transfer::create([
                'amount' => $request->amount, // Amount in cents
                'currency' => $currency,
                'destination' => $stripeAccount->id,
                'metadata' => [
                    'user_id' => $user->id,
                    'withdrawal_type' => 'wallet_withdrawal',
                ],
            ]);

            // Create withdrawal payment record
            $withdrawalPayment = Payment::create([
                'package_id' => null,
                'user_id' => $user->id,
                'stripe_payment_intent_id' => $transfer->id,
                'amount' => -$amount, // Negative amount for withdrawal
                'currency' => $currency,
                'status' => 'pending', // Always set to 'pending' for withdrawal
                'payment_type' => 'withdrawal',
                'refund_reason' => 'Wallet withdrawal',
                'processed_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Withdrawal requested successfully',
                'data' => [
                    'id' => $transfer->id,
                    'amount' => $amount,
                    'currency' => $currency,
                    'status' => $transfer->status,
                    'estimated_arrival' => now()->addDays(2)->toISOString(), // Transfers are typically faster than payouts
                    'created_at' => $withdrawalPayment->created_at->toISOString(),
                ]
            ]);

        } catch (ApiErrorException $e) {
            Log::error('Stripe transfer error: ' . $e->getMessage());
            
            // Provide more user-friendly error messages for common Stripe errors
            $errorMessage = 'Withdrawal failed';
            if (strpos($e->getMessage(), 'external_accounts') !== false) {
                $errorMessage = "Withdrawal failed: Sorry, you don't have any external accounts in that currency ({$currency}). Please add a bank account or debit card in your Stripe Connect dashboard first.";
            } elseif (strpos($e->getMessage(), 'payouts_enabled') !== false) {
                $errorMessage = "Withdrawal failed: Payouts are not enabled for your Stripe Connect account. Please complete your account verification first.";
            } elseif (strpos($e->getMessage(), 'insufficient_funds') !== false) {
                $errorMessage = "Withdrawal failed: Insufficient funds in your Stripe Connect account.";
            } elseif (strpos($e->getMessage(), 'insufficient_balance') !== false) {
                $errorMessage = "Withdrawal failed: Insufficient balance in your wallet.";
            } else {
                $errorMessage = 'Withdrawal failed: ' . $e->getMessage();
            }
            
            return response()->json([
                'status' => 'error',
                'message' => $errorMessage
            ], 500);
        } catch (\Exception $e) {
            Log::error('Withdrawal error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process withdrawal'
            ], 500);
        }
    }

    /**
     * Get withdrawal history
     */
    public function getWithdrawalHistory(Request $request)
    {
        try {
            $user = Auth::user();
            $page = $request->get('page', 1);
            $limit = min($request->get('limit', 20), 100);

            $withdrawals = Payment::where('user_id', $user->id)
                ->where('payment_type', 'withdrawal')
                ->orderBy('created_at', 'desc')
                ->paginate($limit);

            $formattedWithdrawals = $withdrawals->getCollection()->map(function ($payment) {
                return [
                    'id' => $payment->stripe_payment_intent_id,
                    'amount' => abs($payment->amount),
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'estimated_arrival' => $payment->processed_at ? $payment->processed_at->addDays(3)->toISOString() : null,
                    'created_at' => $payment->created_at->toISOString(),
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $formattedWithdrawals,
                'pagination' => [
                    'current_page' => $withdrawals->currentPage(),
                    'total_pages' => $withdrawals->lastPage(),
                    'total_items' => $withdrawals->total(),
                    'per_page' => $withdrawals->perPage(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Withdrawal history error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get withdrawal history'
            ], 500);
        }
    }

    /**
     * Get Stripe Connect account status
     */
    public function getStripeAccountStatus()
    {
        try {
            $user = Auth::user();
            $stripeAccount = $this->getOrCreateStripeAccount($user);

            // Get external accounts
            $externalAccounts = \Stripe\Account::allExternalAccounts(
                $stripeAccount->id,
                ['object' => 'bank_account', 'limit' => 100]
            );

            $currency = config('services.currency');
            $hasExternalAccountForCurrency = false;
            $externalAccountsList = [];

            foreach ($externalAccounts->data as $externalAccount) {
                $externalAccountsList[] = [
                    'id' => $externalAccount->id,
                    'currency' => $externalAccount->currency,
                    'bank_name' => $externalAccount->bank_name,
                    'last4' => $externalAccount->last4,
                    'country' => $externalAccount->country,
                ];
                
                if ($externalAccount->currency === strtolower($currency)) {
                    $hasExternalAccountForCurrency = true;
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'account_id' => $stripeAccount->id,
                    'status' => $stripeAccount->charges_enabled ? 'active' : 'pending',
                    'requirements' => [
                        'currently_due' => $stripeAccount->requirements->currently_due ?? [],
                        'eventually_due' => $stripeAccount->requirements->eventually_due ?? [],
                        'past_due' => $stripeAccount->requirements->past_due ?? [],
                    ],
                    'payouts_enabled' => $stripeAccount->payouts_enabled,
                    'charges_enabled' => $stripeAccount->charges_enabled,
                    'external_accounts' => $externalAccountsList,
                    'has_external_account_for_currency' => $hasExternalAccountForCurrency,
                    'currency' => $currency,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Stripe account status error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get Stripe account status'
            ], 500);
        }
    }

    /**
     * Setup Stripe Connect account
     */
    public function setupStripeAccount(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'country' => 'required|string|size:2',
            'business_type' => 'required|in:individual,company',
            'individual' => 'required_if:business_type,individual|array',
            'company' => 'required_if:business_type,company|array',
        ]);

        try {
            $user = Auth::user();

            // Create Stripe Connect account
            $accountData = [
                'type' => 'express',
                'country' => $request->country,
                'email' => $request->email,
                'business_type' => $request->business_type,
                'capabilities' => [
                    'transfers' => ['requested' => true],
                    'card_payments' => ['requested' => true],
                ],
            ];

            if ($request->business_type === 'individual' && $request->individual) {
                $accountData['individual'] = $request->individual;
            } elseif ($request->business_type === 'company' && $request->company) {
                $accountData['company'] = $request->company;
            }

            $stripeAccount = Account::create($accountData);

            // Update user with Stripe account ID
            $user->update(['stripe_account_id' => $stripeAccount->id]);

            // Create account link for onboarding
            $accountLink = \Stripe\AccountLink::create([
                'account' => $stripeAccount->id,
                'refresh_url' => config('app.url') . '/wallet/connect/refresh?account_id=' . $stripeAccount->id,
                'return_url' => config('app.url') . '/wallet/connect/return?account_id=' . $stripeAccount->id,
                'type' => 'account_onboarding',
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'account_id' => $stripeAccount->id,
                    'account_link' => $accountLink->url,
                ]
            ]);

        } catch (ApiErrorException $e) {
            Log::error('Stripe account creation error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to setup Stripe account: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Stripe account setup error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to setup Stripe account'
            ], 500);
        }
    }

    /**
     * Get minimum withdrawal amount
     */
    public function getMinimumWithdrawalAmount()
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'amount' => 100, // 100kr minimum
                'currency' => config('services.currency'),
            ]
        ]);
    }

    /**
     * Get main Stripe account balance (for debugging)
     */
    public function getMainStripeAccountBalance()
    {
        try {
            $balance = \Stripe\Balance::retrieve();
            $currency = config('services.currency');
            
            $availableBalance = 0;
            $pendingBalance = 0;
            
            foreach ($balance->available as $available) {
                if ($available->currency === strtolower($currency)) {
                    $availableBalance = $available->amount;
                }
            }
            
            foreach ($balance->pending as $pending) {
                if ($pending->currency === strtolower($currency)) {
                    $pendingBalance = $pending->amount;
                }
            }
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'currency' => $currency,
                    'available_balance' => $availableBalance / 100, // Convert from cents
                    'pending_balance' => $pendingBalance / 100, // Convert from cents
                    'total_balance' => ($availableBalance + $pendingBalance) / 100, // Convert from cents
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get main account balance: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get main account balance'
            ], 500);
        }
    }

    /**
     * Get Stripe Connect dashboard link for managing external accounts
     */
    public function getStripeDashboardLink()
    {
        try {
            $user = Auth::user();
            $stripeAccount = $this->getOrCreateStripeAccount($user);

            // Create account link for dashboard access
            $accountLink = \Stripe\AccountLink::create([
                'account' => $stripeAccount->id,
                'refresh_url' => config('app.url') . '/wallet/connect/refresh?account_id=' . $stripeAccount->id,
                'return_url' => config('app.url') . '/wallet/connect/return?account_id=' . $stripeAccount->id,
                'type' => 'account_onboarding',
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'dashboard_url' => $accountLink->url,
                    'account_id' => $stripeAccount->id,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Stripe dashboard link error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get Stripe dashboard link'
            ], 500);
        }
    }

    /**
     * Release payment from escrow when sender completes delivery
     */
    public function releasePayment($orderId)
    {
        try {
            $order = Order::with(['package', 'dropper'])->findOrFail($orderId);
            
            // Check if order is completed
            if ($order->status !== 'completed') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order is not completed'
                ], 400);
            }

            // Find the escrow payment for this package
            $escrowPayment = Payment::where('package_id', $order->package_id)
                ->where('payment_type', 'escrow')
                ->where('status', 'succeeded')
                ->first();

            if (!$escrowPayment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No escrow payment found for this package'
                ], 404);
            }

            // Check if payment already released
            $existingRelease = Payment::where('package_id', $order->package_id)
                ->where('payment_type', 'release')
                ->first();

            if ($existingRelease) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment already released for this package'
                ], 400);
            }

            // Create release payment record
            $riderAmount = round($escrowPayment->amount * 0.908, 2); // 90.8% to rider
            $commissionAmount = round($escrowPayment->amount * 0.092, 2); // 9.2% commission

            $releasePayment = Payment::create([
                'package_id' => $order->package_id,
                'user_id' => $order->dropper_id,
                'stripe_payment_intent_id' => 'release_' . $escrowPayment->stripe_payment_intent_id,
                'amount' => $riderAmount,
                'currency' => $escrowPayment->currency,
                'status' => 'succeeded',
                'payment_type' => 'release',
                'processed_at' => now(),
            ]);

            // Record platform commission (optional, for tracking)
            Payment::create([
                'package_id' => $order->package_id,
                'user_id' => 1, // or platform user ID if you have one
                'stripe_payment_intent_id' => 'commission_' . $escrowPayment->stripe_payment_intent_id,
                'amount' => $commissionAmount,
                'currency' => $escrowPayment->currency,
                'status' => 'succeeded',
                'payment_type' => 'commission',
                'processed_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment released successfully',
                'data' => [
                    'amount' => $releasePayment->amount,
                    'currency' => $releasePayment->currency,
                    'dropper_id' => $order->dropper_id,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Payment release error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to release payment'
            ], 500);
        }
    }

    /**
     * Helper method to get available balance
     */
    private function getAvailableBalance($user)
    {
        $completedPayments = Payment::where('user_id', $user->id)
            ->where('payment_type', 'release')
            ->where('status', 'succeeded')
            ->sum('amount');

        $withdrawals = Payment::where('user_id', $user->id)
            ->where('payment_type', 'withdrawal')
            ->where('status', 'succeeded')
            ->sum('amount');

        return max(0, $completedPayments - abs($withdrawals));
    }

    /**
     * Helper method to get or create Stripe Connect account
     */
    private function getOrCreateStripeAccount($user)
    {
        if ($user->stripe_account_id) {
            try {
                return Account::retrieve($user->stripe_account_id);
            } catch (ApiErrorException $e) {
                // Account doesn't exist, create new one
                $user->update(['stripe_account_id' => null]);
            }
        }

        // Create new account
        $account = Account::create([
            'type' => 'express',
            'country' => 'SE', // Default country - Sweden
            'email' => $user->email,
            'business_type' => 'individual',
            'capabilities' => [
                'transfers' => ['requested' => true],
                'card_payments' => ['requested' => true],
            ],
        ]);

        $user->update(['stripe_account_id' => $account->id]);
        return $account;
    }

    /**
     * Helper method to get transaction description
     */
    private function getTransactionDescription($payment)
    {
        switch ($payment->payment_type) {
            case 'release':
                return "Payment for package #{$payment->package_id}";
            case 'withdrawal':
                return "Withdrawal to bank account";
            case 'refund':
                return "Refund for package #{$payment->package_id}";
            default:
                return "Transaction #{$payment->id}";
        }
    }

    /**
     * Helper method to get main Stripe account balance
     */
    private function getMainAccountBalance($currency)
    {
        try {
            $balance = \Stripe\Balance::retrieve();
            
            foreach ($balance->available as $availableBalance) {
                if ($availableBalance->currency === strtolower($currency)) {
                    return $availableBalance->amount;
                }
            }
            
            return 0; // No balance found for this currency
        } catch (\Exception $e) {
            Log::error('Failed to get main account balance: ' . $e->getMessage());
            return 0;
        }
    }
} 