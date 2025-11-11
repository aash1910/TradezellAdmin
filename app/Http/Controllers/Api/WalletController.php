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
use App\Services\CurrencyConversionService;
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
            $now = now();
            
            // Calculate available balance
            // Include payments where:
            // 1. Status is 'succeeded' AND
            // 2. available_on is NULL (old records) OR available_on is in the past
            $completedPayments = Payment::where('user_id', $user->id)
                ->where('payment_type', 'release')
                ->where('status', 'succeeded')
                ->where(function($query) use ($now) {
                    $query->whereNull('available_on')
                          ->orWhere('available_on', '<=', $now);
                })
                ->sum('amount');

            $withdrawals = Payment::where('user_id', $user->id)
                ->where('payment_type', 'withdrawal')
                ->where('status', 'succeeded')
                ->sum('amount');

            $availableBalance = $completedPayments - abs($withdrawals);

            // Calculate pending balance
            // Include payments where:
            // 1. Status is 'succeeded' BUT available_on is in the future, OR
            // 2. Status is 'pending' (not yet released from escrow)
            $pendingPayments = Payment::where('user_id', $user->id)
                ->where('payment_type', 'release')
                ->where(function($query) use ($now) {
                    // Succeeded but not yet available (Stripe holding period)
                    $query->where(function($q) use ($now) {
                        $q->where('status', 'succeeded')
                          ->where('available_on', '>', $now);
                    })
                    // Or still pending release from escrow
                    ->orWhere('status', 'pending');
                })
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
                    'available_on' => $payment->available_on?->toISOString(),
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
                    'available_on' => $payment->available_on?->toISOString(),
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

            // Check main Stripe account balance (in SEK)
            // Convert USD withdrawal request to SEK for balance checking
            $conversionService = new CurrencyConversionService();
            $amountInSek = $conversionService->convertUsdToSek($request->amount);
            
            // Get actual SEK balance from Stripe
            $sekBalance = $this->getMainAccountBalance('sek');
            
            if ($sekBalance < $amountInSek) {
                // Check if there's a pending balance that will cover the shortfall
                $availableDate = $this->getPendingBalanceAvailableDate('sek', $amountInSek);
                
                $message = $availableDate 
                    ? "You can withdraw funds on {$availableDate}"
                    : 'Insufficient funds in main account to process withdrawal';
                
                return response()->json([
                    'status' => 'error',
                    'message' => $message
                ], 400);
            }

            // Get or create Stripe Connect account
            $stripeAccount = $this->getOrCreateStripeAccount($user);

            // Check if account has any external accounts (support multi-currency)
            $externalAccounts = \Stripe\Account::allExternalAccounts(
                $stripeAccount->id,
                ['limit' => 100]
            );

            if (count($externalAccounts->data) === 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Withdrawal failed: You don't have any external accounts (bank account or debit card) linked to your Stripe Connect account. Please add one first."
                ], 400);
            }

            // Get the first external account's currency for the transfer
            $externalAccount = $externalAccounts->data[0];
            $transferCurrency = 'sek'; // Always use SEK since that's what we have in Stripe

            // Log currency conversion info
            Log::info("Processing withdrawal for user {$user->id}: {$request->amount} USD cents ({$amount} USD) converted to {$amountInSek} SEK öre");

            // Create transfer to Stripe Connect account using SEK
            $transfer = Transfer::create([
                'amount' => $amountInSek,
                'currency' => $transferCurrency,
                'destination' => $stripeAccount->id,
                'metadata' => [
                    'user_id' => $user->id,
                    'withdrawal_type' => 'wallet_withdrawal',
                    'system_currency' => $currency,
                    'transfer_currency' => $transferCurrency,
                    'requested_amount_usd' => $request->amount,
                    'converted_amount_sek' => $amountInSek,
                ],
            ]);

            // Create withdrawal payment record using the transfer currency
            $withdrawalPayment = Payment::create([
                'package_id' => null,
                'user_id' => $user->id,
                'stripe_payment_intent_id' => $transfer->id,
                'amount' => -$amount, // Negative amount for withdrawal
                'currency' => $transferCurrency,
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
                    'currency' => $transferCurrency,
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
                $errorMessage = "Withdrawal failed: You don't have any external accounts (bank account or debit card) linked. Please add one in your Stripe Connect dashboard first.";
            } elseif (strpos($e->getMessage(), 'payouts_enabled') !== false) {
                $errorMessage = "Withdrawal failed: Payouts are not enabled for your Stripe Connect account. Please complete your account verification first.";
            } elseif (strpos($e->getMessage(), 'insufficient_funds') !== false) {
                $errorMessage = "Withdrawal failed: Insufficient funds in the main account.";
            } elseif (strpos($e->getMessage(), 'insufficient_balance') !== false) {
                $errorMessage = "Withdrawal failed: Insufficient balance in your wallet.";
            } elseif (strpos($e->getMessage(), 'currency') !== false) {
                $errorMessage = "Withdrawal failed: Currency mismatch. Please ensure your bank account currency matches the withdrawal currency.";
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

            $systemCurrency = config('services.currency');
            $hasExternalAccountForCurrency = false;
            $hasAnyExternalAccount = false;
            $externalAccountsList = [];
            $primaryExternalAccountCurrency = null;

            // Get all external accounts (both bank accounts and cards)
            $allExternalAccounts = \Stripe\Account::allExternalAccounts(
                $stripeAccount->id,
                ['limit' => 100]
            );

            // Process all external accounts
            foreach ($allExternalAccounts->data as $index => $externalAccount) {
                $accountData = [
                    'id' => $externalAccount->id,
                    'object' => $externalAccount->object,
                    'currency' => $externalAccount->currency ?? null,
                    'last4' => $externalAccount->last4 ?? null,
                    'country' => $externalAccount->country ?? null,
                ];

                // Add type-specific fields
                if ($externalAccount->object === 'bank_account') {
                    $accountData['bank_name'] = $externalAccount->bank_name ?? null;
                    $accountData['account_holder_type'] = $externalAccount->account_holder_type ?? null;
                } elseif ($externalAccount->object === 'card') {
                    $accountData['brand'] = $externalAccount->brand ?? null;
                    $accountData['exp_month'] = $externalAccount->exp_month ?? null;
                    $accountData['exp_year'] = $externalAccount->exp_year ?? null;
                }

                $externalAccountsList[] = $accountData;
                $hasAnyExternalAccount = true;
                
                // Store the first external account's currency
                if ($index === 0 && isset($externalAccount->currency)) {
                    $primaryExternalAccountCurrency = strtoupper($externalAccount->currency);
                }
                
                // Check if this external account matches the system currency
                if (isset($externalAccount->currency) && 
                    strtolower($externalAccount->currency) === strtolower($systemCurrency)) {
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
                    'has_external_account_for_currency' => $hasAnyExternalAccount, // Changed to check for ANY external account
                    'currency' => $primaryExternalAccountCurrency ?? $systemCurrency, // Use external account currency if available
                    'system_currency' => $systemCurrency,
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
     * Helper method to get available balance
     */
    private function getAvailableBalance($user)
    {
        $now = now();
        
        // Only include payments that are actually available (not in Stripe holding period)
        $completedPayments = Payment::where('user_id', $user->id)
            ->where('payment_type', 'release')
            ->where('status', 'succeeded')
            ->where(function($query) use ($now) {
                $query->whereNull('available_on')
                      ->orWhere('available_on', '<=', $now);
            })
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

    /**
     * Helper method to get the date when pending balance will be available
     * 
     * @param string $currency The currency code
     * @param int $requiredAmount The amount needed (in cents)
     * @return string|null The formatted date when funds will be available, or null if not available
     */
    private function getPendingBalanceAvailableDate($currency, $requiredAmount)
    {
        try {
            $balance = \Stripe\Balance::retrieve();
            
            // Get current available balance
            $currentAvailable = 0;
            foreach ($balance->available as $availableBalance) {
                if ($availableBalance->currency === strtolower($currency)) {
                    $currentAvailable = $availableBalance->amount;
                    break;
                }
            }
            
            // Get pending balance for this currency
            $totalPending = 0;
            foreach ($balance->pending as $pending) {
                if ($pending->currency === strtolower($currency)) {
                    $totalPending += $pending->amount;
                }
            }
            
            // Log for debugging
            Log::info("Withdrawal check - Available: {$currentAvailable}, Pending: {$totalPending}, Required: {$requiredAmount}");
            
            // If there's pending balance, get the actual settlement date from BalanceTransactions
            if ($totalPending > 0) {
                try {
                    // Retrieve recent balance transactions to find pending ones with available_on dates
                    $transactions = \Stripe\BalanceTransaction::all([
                        'limit' => 100,
                    ]);
                    
                    $earliestAvailableOn = null;
                    
                    foreach ($transactions->data as $transaction) {
                        // Check if this transaction is for our currency and has a future available_on date
                        if ($transaction->currency === strtolower($currency) && 
                            isset($transaction->available_on) && 
                            $transaction->available_on > time()) {
                            
                            if ($earliestAvailableOn === null || $transaction->available_on < $earliestAvailableOn) {
                                $earliestAvailableOn = $transaction->available_on;
                            }
                        }
                    }
                    
                    if ($earliestAvailableOn !== null) {
                        // Convert Unix timestamp to Carbon date
                        $availableDate = Carbon::createFromTimestamp($earliestAvailableOn);
                        Log::info("Found earliest available_on date: " . $availableDate->format('Y-m-d H:i:s'));
                        
                        // Format the date as "3 Nov" style
                        return $availableDate->format('j M');
                    }
                    
                } catch (\Exception $e) {
                    Log::warning('Failed to retrieve balance transactions: ' . $e->getMessage());
                }
            }
            
            return null; // No pending balance available
            
        } catch (\Exception $e) {
            Log::error('Failed to get pending balance available date: ' . $e->getMessage());
            return null;
        }
    }
} 