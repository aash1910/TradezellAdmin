<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;
use App\Models\Package;
use App\User;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Handle Stripe webhook events
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (SignatureVerificationException $e) {
            Log::error('Webhook signature verification failed: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;
            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;
            case 'charge.refunded':
                $this->handleChargeRefunded($event->data->object);
                break;
            case 'payout.paid':
                $this->handlePayoutPaid($event->data->object);
                break;
            case 'payout.failed':
                $this->handlePayoutFailed($event->data->object);
                break;
            case 'account.updated':
                $this->handleAccountUpdated($event->data->object);
                break;
            case 'transfer.updated':
                $this->handleTransferUpdated($event->data->object);
                break;
            case 'transfer.created':
                $this->handleTransferCreated($event->data->object);
                break;
            default:
                Log::info('Unhandled event type: ' . $event->type);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle successful payment intent
     */
    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        try {
            $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();
            
            if ($payment) {
                $payment->update([
                    'status' => $paymentIntent->status,
                    'processed_at' => now(),
                ]);

                Log::info('Payment succeeded: ' . $paymentIntent->id);
            }
        } catch (\Exception $e) {
            Log::error('Error handling payment_intent.succeeded: ' . $e->getMessage());
        }
    }

    /**
     * Handle failed payment intent
     */
    private function handlePaymentIntentFailed($paymentIntent)
    {
        try {
            $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();
            
            if ($payment) {
                $payment->update([
                    'status' => 'failed',
                    'processed_at' => now(),
                ]);

                Log::info('Payment failed: ' . $paymentIntent->id);
            }
        } catch (\Exception $e) {
            Log::error('Error handling payment_intent.payment_failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle charge refunded
     */
    private function handleChargeRefunded($charge)
    {
        try {
            Log::info('Charge refunded webhook received: ' . $charge->id);
            Log::info('Payment intent ID: ' . $charge->payment_intent);
            Log::info('Refund amount: ' . $charge->amount_refunded);
            
            // Find the original payment by payment intent ID
            $payment = Payment::where('stripe_payment_intent_id', $charge->payment_intent)->first();
            
            if ($payment) {
                // Get the refund ID from the charge
                $refundId = null;
                
                // Try to get refund ID from charge.refunds.data array
                if (isset($charge->refunds) && isset($charge->refunds->data) && count($charge->refunds->data) > 0) {
                    $refundId = $charge->refunds->data[0]->id;
                }
                
                // If no refund ID found, try to list refunds for this charge
                if (!$refundId) {
                    try {
                        $refunds = \Stripe\Refund::all(['charge' => $charge->id, 'limit' => 1]);
                        if (count($refunds->data) > 0) {
                            $refundId = $refunds->data[0]->id;
                        }
                    } catch (\Exception $e) {
                        Log::error('Error listing refunds for charge: ' . $e->getMessage());
                    }
                }
                
                if (!$refundId) {
                    Log::warning('No refund ID found in charge: ' . $charge->id);
                    return;
                }
                
                // Get the refund object from Stripe to get the correct status and metadata
                try {
                    $refund = \Stripe\Refund::retrieve($refundId);
                } catch (\Exception $e) {
                    Log::error('Error retrieving refund from Stripe: ' . $e->getMessage());
                    return;
                }
                
                // Check if refund payment record already exists
                $existingRefund = Payment::where('stripe_payment_intent_id', $refundId)
                    ->where('payment_type', 'refund')
                    ->first();
                
                if (!$existingRefund) {
                    // Build refund reason with original reason if available
                    $originalReason = $refund->metadata->reason ?? 'No reason provided';
                    $refundReason = 'Refunded from Stripe Dashboard - Original reason: ' . $originalReason;
                    
                    // Create refund payment record
                    $refundPayment = Payment::create([
                        'package_id' => $payment->package_id,
                        'user_id' => $payment->user_id,
                        'stripe_payment_intent_id' => $refundId,
                        'amount' => -$payment->amount, // Negative amount for refund
                        'currency' => $payment->currency,
                        'status' => $refund->status,
                        'payment_type' => 'refund',
                        'refund_reason' => $refundReason,
                        'processed_at' => now(),
                    ]);

                    Log::info('Refund payment record created: ' . $refundPayment->id);
                } else {
                    Log::info('Refund payment record already exists: ' . $existingRefund->id);
                }
            } else {
                Log::warning('No payment found for payment intent: ' . $charge->payment_intent);
            }
        } catch (\Exception $e) {
            Log::error('Error handling charge.refunded: ' . $e->getMessage());
        }
    }

    /**
     * Handle successful payout
     */
    private function handlePayoutPaid($payout)
    {
        try {
            $payment = Payment::where('stripe_payment_intent_id', $payout->id)->first();
            
            if ($payment) {
                $payment->update([
                    'status' => 'succeeded',
                    'processed_at' => now(),
                ]);

                Log::info('Payout succeeded: ' . $payout->id);
            }
        } catch (\Exception $e) {
            Log::error('Error handling payout.paid: ' . $e->getMessage());
        }
    }

    /**
     * Handle failed payout
     */
    private function handlePayoutFailed($payout)
    {
        try {
            $payment = Payment::where('stripe_payment_intent_id', $payout->id)->first();
            
            if ($payment) {
                $payment->update([
                    'status' => 'failed',
                    'processed_at' => now(),
                ]);

                Log::info('Payout failed: ' . $payout->id);
            }
        } catch (\Exception $e) {
            Log::error('Error handling payout.failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle Stripe Connect account updates
     */
    private function handleAccountUpdated($account)
    {
        try {
            $user = User::where('stripe_account_id', $account->id)->first();
            
            if ($user) {
                // Update user account status if needed
                Log::info('Stripe Connect account updated: ' . $account->id);
            }
        } catch (\Exception $e) {
            Log::error('Error handling account.updated: ' . $e->getMessage());
        }
    }

    /**
     * Handle updated transfer (for wallet withdrawals)
     */
    private function handleTransferUpdated($transfer)
    {
        try {
            $payment = Payment::where('stripe_payment_intent_id', $transfer->id)->first();
            if ($payment && isset($transfer->status) && in_array($transfer->status, ['paid', 'succeeded'])) {
                $payment->update([
                    'status' => 'succeeded',
                    'processed_at' => now(),
                ]);
                Log::info('Transfer succeeded: ' . $transfer->id);
            }
        } catch (\Exception $e) {
            Log::error('Error handling transfer.updated: ' . $e->getMessage());
        }
    }

    /**
     * Handle created transfer (for wallet withdrawals)
     */
    private function handleTransferCreated($transfer)
    {
        try {
            Log::info('Transfer created webhook received:', (array) $transfer);
            $payment = Payment::where('stripe_payment_intent_id', $transfer->id)->first();
            if ($payment) {
                $payment->update([
                    'status' => 'succeeded',
                    'processed_at' => now(),
                ]);
                Log::info('Transfer succeeded (created event): ' . $transfer->id);
            }
        } catch (\Exception $e) {
            Log::error('Error handling transfer.created: ' . $e->getMessage());
        }
    }
} 