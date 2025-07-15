<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;
use App\Models\Package;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Exception\ApiErrorException;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a payment intent for escrow
     */
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'package_id' => 'nullable|exists:packages,id',
            'package_data' => 'nullable|array',
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|size:3',
        ]);

        // Either package_id or package_data must be provided
        if (!$request->package_id && !$request->package_data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Either package_id or package_data is required'
            ], 422);
        }

        try {
            $user = Auth::user();
            $package = null;
            $packageId = null;

            if ($request->package_id) {
                // Existing package flow
                $package = Package::findOrFail($request->package_id);
                
                // Check if user owns the package
                if ($package->sender_id !== $user->id) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Unauthorized access to package'
                    ], 403);
                }

                // Check if payment already exists
                $existingPayment = Payment::where('package_id', $package->id)
                    ->where('payment_type', 'escrow')
                    ->where('status', 'succeeded')
                    ->first();

                if ($existingPayment) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Payment already exists for this package'
                    ], 400);
                }

                $packageId = $package->id;
            } else {
                // Pre-package payment flow - package will be created after payment
                $packageId = null;
            }

            // Create Stripe Payment Intent
            $currency = config('services.currency');
            $metadata = [
                'user_id' => $user->id,
                'payment_type' => 'escrow'
            ];

            if ($packageId) {
                $metadata['package_id'] = $packageId;
            } else {
                // Only add minimal info for new package payments
                if (!empty($request->package_data['pickup_name'])) {
                    $metadata['pickup_name'] = substr($request->package_data['pickup_name'], 0, 50);
                }
                if (!empty($request->package_data['drop_name'])) {
                    $metadata['drop_name'] = substr($request->package_data['drop_name'], 0, 50);
                }
                // Do NOT add the full package_data JSON to metadata
            }

            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount,
                'currency' => $request->currency ? $request->currency : $currency,
                'metadata' => $metadata,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            // Create payment record
            $paymentData = [
                'user_id' => $user->id,
                'stripe_payment_intent_id' => $paymentIntent->id,
                'amount' => $request->amount / 100, // Convert from cents
                'currency' => $request->currency ? $request->currency : $currency,
                'status' => $paymentIntent->status,
                'payment_type' => 'escrow',
            ];

            if ($packageId) {
                $paymentData['package_id'] = $packageId;
            }

            $payment = Payment::create($paymentData);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $paymentIntent->id,
                    'amount' => $paymentIntent->amount,
                    'currency' => $paymentIntent->currency,
                    'status' => $paymentIntent->status,
                    'client_secret' => $paymentIntent->client_secret,
                ]
            ]);

        } catch (ApiErrorException $e) {
            Log::error('Stripe API Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Payment service error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Payment Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Create package after successful payment
     */
    public function createPackageAfterPayment(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'package_data' => 'required|array',
            'package_data.pickup_name' => 'required|string',
            'package_data.pickup_mobile' => 'required|string',
            'package_data.pickup_address' => 'required|string',
            'package_data.pickup_address2' => 'nullable|string',
            'package_data.pickup_address3' => 'nullable|string',
            'package_data.pickup_details' => 'nullable|string',
            'package_data.weight' => 'required|numeric|min:0.01',
            'package_data.price' => 'required|numeric|min:0.01',
            'package_data.pickup_date' => 'required|date|after_or_equal:today',
            'package_data.pickup_time' => 'required|date_format:H:i',
            'package_data.drop_name' => 'required|string',
            'package_data.drop_mobile' => 'required|string',
            'package_data.drop_address' => 'required|string',
            'package_data.drop_address2' => 'nullable|string',
            'package_data.drop_address3' => 'nullable|string',
            'package_data.drop_details' => 'nullable|string',
            'package_data.pickup_lat' => 'nullable|numeric',
            'package_data.pickup_lng' => 'nullable|numeric',
            'package_data.pickup_lat2' => 'nullable|numeric',
            'package_data.pickup_lng2' => 'nullable|numeric',
            'package_data.pickup_lat3' => 'nullable|numeric',
            'package_data.pickup_lng3' => 'nullable|numeric',
            'package_data.drop_lat' => 'nullable|numeric',
            'package_data.drop_lng' => 'nullable|numeric',
            'package_data.drop_lat2' => 'nullable|numeric',
            'package_data.drop_lng2' => 'nullable|numeric',
            'package_data.drop_lat3' => 'nullable|numeric',
            'package_data.drop_lng3' => 'nullable|numeric',
            'package_data.pickup_image' => 'nullable|string',
        ], $this->getValidationMessages());

        try {
            $payment = Payment::where('stripe_payment_intent_id', $request->payment_intent_id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$payment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment not found'
                ], 404);
            }

            if (strtolower($payment->status) !== 'succeeded') {
                // Payment not yet succeeded, likely waiting for Stripe webhook
                return response()->json([
                    'status' => 'pending',
                    'message' => 'Payment is processing. Please retry in a few seconds.'
                ], 202);
            }

            // Check if package already exists for this payment
            if ($payment->package_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Package already exists for this payment'
                ], 400);
            }

            // Create the package
            $packageData = $request->package_data;
            Log::info('Package data received in createPackageAfterPayment:', $packageData);
            
            $packageData['sender_id'] = Auth::id();
            
            $package = Package::create($packageData);
            
            // Update payment record with package_id
            $payment->update(['package_id' => $package->id]);
            
            $package->load('sender:id,image');

            return response()->json([
                'status' => 'success',
                'message' => 'Package created successfully',
                'data' => [
                    'id' => $package->id,
                    'sender' => [
                        'id' => $package->sender->id,
                        'image' => $package->sender->image,
                    ],
                    'weight' => $package->weight,
                    'price' => $package->price,
                    'pickup' => [
                        'name' => $package->pickup_name,
                        'mobile' => $package->pickup_mobile,
                        'address' => $package->pickup_address,
                        'address2' => $package->pickup_address2,
                        'address3' => $package->pickup_address3,
                        'details' => $package->pickup_details,
                        'date' => date('Y-m-d', strtotime($package->pickup_date)),
                        'time' => date('H:i', strtotime($package->pickup_time)),
                        'image' => $package->pickup_image,
                        'coordinates' => [
                            'lat' => $package->pickup_lat,
                            'lng' => $package->pickup_lng,
                            'lat2' => $package->pickup_lat2,
                            'lng2' => $package->pickup_lng2,
                            'lat3' => $package->pickup_lat3,
                            'lng3' => $package->pickup_lng3,
                        ],
                    ],
                    'drop' => [
                        'name' => $package->drop_name,
                        'mobile' => $package->drop_mobile,
                        'address' => $package->drop_address,
                        'address2' => $package->drop_address2,
                        'address3' => $package->drop_address3,
                        'details' => $package->drop_details,
                        'coordinates' => [
                            'lat' => $package->drop_lat,
                            'lng' => $package->drop_lng,
                            'lat2' => $package->drop_lat2,
                            'lng2' => $package->drop_lng2,
                            'lat3' => $package->drop_lat3,
                            'lng3' => $package->drop_lng3,
                        ],
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Package Creation After Payment Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create package after payment'
            ], 500);
        }
    }

    /**
     * Get custom validation messages
     */
    private function getValidationMessages()
    {
        return [
            'package_data.pickup_date.after_or_equal' => 'The pickup date must be today or a future date',
            'package_data.pickup_time.date_format' => 'The pickup time must be in 24-hour format (HH:mm)',
        ];
    }

    /**
     * Confirm payment
     */
    public function confirmPayment(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'payment_method_id' => 'required|string',
        ]);

        try {
            $payment = Payment::where('stripe_payment_intent_id', $request->payment_intent_id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$payment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment not found'
                ], 404);
            }

            // Retrieve the payment intent from Stripe
            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

            // Update payment record
            $payment->update([
                'stripe_payment_method_id' => $request->payment_method_id,
                'status' => $paymentIntent->status,
                'processed_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment confirmed successfully',
                'data' => [
                    'status' => $paymentIntent->status,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                ]
            ]);

        } catch (ApiErrorException $e) {
            Log::error('Stripe API Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Payment confirmation failed: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Payment Confirmation Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus($paymentIntentId)
    {
        try {
            $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$payment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'status' => $payment->status,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'payment_type' => $payment->payment_type,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Payment Status Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Request refund for escrow
     */
    public function requestRefund(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $package = Package::with('order.dropper')->findOrFail($request->package_id);
            $user = Auth::user();

            // Check if user is either the sender or the assigned dropper
            $isSender = $package->sender_id === $user->id;
            $isDropper = $package->order && $package->order->dropper_id === $user->id;
            
            if (!$isSender && !$isDropper) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access to package'
                ], 403);
            }

            // Find the escrow payment
            $escrowPayment = Payment::where('package_id', $package->id)
                ->where('payment_type', 'escrow')
                ->where('status', 'succeeded')
                ->first();

            if (!$escrowPayment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No escrow payment found for this package'
                ], 404);
            }

            // Check if refund already exists
            $existingRefund = Payment::where('package_id', $package->id)
                ->where('payment_type', 'refund')
                ->first();

            if ($existingRefund) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Refund already processed for this package'
                ], 400);
            }

            // Create Stripe refund
            $refund = Refund::create([
                'payment_intent' => $escrowPayment->stripe_payment_intent_id,
                'metadata' => [
                    'package_id' => $package->id,
                    'user_id' => $package->sender_id, // Always use sender's ID for refund
                    'reason' => $request->reason ?? 'No dropper assigned',
                ],
            ]);

            // Create refund payment record
            $refundPayment = Payment::create([
                'package_id' => $package->id,
                'user_id' => $package->sender_id, // Always use sender's ID for refund
                'stripe_payment_intent_id' => $refund->id,
                'amount' => -$escrowPayment->amount, // Negative amount for refund
                'currency' => $escrowPayment->currency,
                'status' => $refund->status,
                'payment_type' => 'refund',
                'refund_reason' => $request->reason ?? 'No dropper assigned',
                'processed_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Refund processed successfully',
                'data' => [
                    'status' => $refund->status,
                    'amount' => $refundPayment->amount,
                    'currency' => $refundPayment->currency,
                    'reason' => $refundPayment->refund_reason,
                ]
            ]);

        } catch (ApiErrorException $e) {
            Log::error('Stripe Refund Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Refund processing failed: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Refund Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get payment history for user
     */
    public function getPaymentHistory()
    {
        try {
            $payments = Payment::where('user_id', Auth::id())
                ->with(['package'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'currency' => $payment->currency,
                        'status' => $payment->status,
                        'payment_type' => $payment->payment_type,
                        'refund_reason' => $payment->refund_reason,
                        'processed_at' => $payment->processed_at,
                        'package' => $payment->package ? [
                            'id' => $payment->package->id,
                            'pickup_address' => $payment->package->pickup_address,
                            'drop_address' => $payment->package->drop_address,
                        ] : null,
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Payment History Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get payment details for a specific package
     */
    public function getPackagePayment($packageId)
    {
        try {
            $package = Package::findOrFail($packageId);
            $user = Auth::user();

            // Check if user owns the package
            if ($package->sender_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access to package'
                ], 403);
            }

            $payments = Payment::where('package_id', $packageId)
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'currency' => $payment->currency,
                        'status' => $payment->status,
                        'payment_type' => $payment->payment_type,
                        'refund_reason' => $payment->refund_reason,
                        'processed_at' => $payment->processed_at,
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Package Payment Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Release payment from escrow when sender completes delivery
     */
    public function releasePaymentFromEscrow($packageId)
    {
        try {
            $user = Auth::user();
            
            // Find the package and verify sender owns it
            $package = Package::where('id', $packageId)
                ->where('sender_id', $user->id)
                ->with(['order.dropper'])
                ->first();

            if (!$package) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Package not found or you are not the sender'
                ], 404);
            }

            // Check if order is completed
            if (!$package->order || $package->order->status !== 'completed') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order is not completed yet'
                ], 400);
            }

            // Find the escrow payment
            $escrowPayment = Payment::where('package_id', $packageId)
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
            $existingRelease = Payment::where('package_id', $packageId)
                ->where('payment_type', 'release')
                ->first();

            if ($existingRelease) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment already released for this package'
                ], 400);
            }

            // Create release payment record for the dropper
            $releasePayment = Payment::create([
                'package_id' => $packageId,
                'user_id' => $package->order->dropper_id,
                'stripe_payment_intent_id' => 'release_' . $escrowPayment->stripe_payment_intent_id,
                'amount' => $escrowPayment->amount,
                'currency' => $escrowPayment->currency,
                'status' => 'succeeded',
                'payment_type' => 'release',
                'processed_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment released to dropper successfully',
                'data' => [
                    'amount' => $releasePayment->amount,
                    'currency' => $releasePayment->currency,
                    'dropper_id' => $package->order->dropper_id,
                    'released_at' => $releasePayment->processed_at->toISOString(),
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
} 