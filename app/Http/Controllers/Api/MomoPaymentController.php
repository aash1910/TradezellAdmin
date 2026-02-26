<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Payment;
use App\Services\MomoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MomoPaymentController extends Controller
{
    private MomoService $momoService;

    public function __construct(MomoService $momoService)
    {
        $this->momoService = $momoService;
    }

    /**
     * Initiate a MoMo Request to Pay (creates an escrow payment record).
     * The user receives a USSD push on their phone to approve the payment.
     *
     * POST /api/momo/request-to-pay
     */
    public function requestToPay(Request $request)
    {
        $request->validate([
            'amount'       => 'required|numeric|min:0.01',
            'phone'        => 'required|string',
            'currency'     => 'nullable|string|size:3',
            'package_id'   => 'nullable|exists:packages,id',
            'package_data' => 'nullable|array',
        ]);

        if (!$request->package_id && !$request->package_data) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Either package_id or package_data is required',
            ], 422);
        }

        try {
            $user        = Auth::user();
            $referenceId = Str::uuid()->toString();
            // Always use server-configured MoMo currency (e.g. EUR for sandbox, XAF for Cameroon production)
            $currency    = config('services.momo.currency', 'EUR');
            $packageId   = null;

            if ($request->package_id) {
                $package = Package::findOrFail($request->package_id);

                if ($package->sender_id !== $user->id) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Unauthorized access to package',
                    ], 403);
                }

                $existingPayment = Payment::where('package_id', $package->id)
                    ->where('payment_type', 'escrow')
                    ->where('status', 'succeeded')
                    ->first();

                if ($existingPayment) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Payment already exists for this package',
                    ], 400);
                }

                $packageId = $package->id;
            }

            $this->momoService->requestToPay(
                (float) $request->amount,
                $request->phone,
                $referenceId,
                $currency
            );

            Payment::create([
                'user_id'           => $user->id,
                'package_id'        => $packageId,
                'payment_gateway'   => 'momo',
                'momo_reference_id' => $referenceId,
                'momo_phone_number' => $request->phone,
                'amount'            => $request->amount,
                'currency'          => $currency,
                'status'            => 'pending',
                'payment_type'      => 'escrow',
            ]);

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'reference_id' => $referenceId,
                    'amount'       => $request->amount,
                    'currency'     => $currency,
                    'status'       => 'pending',
                    'message'      => 'Please approve the payment on your mobile phone.',
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('MoMo requestToPay error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to initiate MoMo payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Poll MoMo API for payment status and sync to local record.
     *
     * GET /api/momo/status/{referenceId}
     */
    public function getStatus(string $referenceId)
    {
        try {
            $payment = Payment::where('momo_reference_id', $referenceId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$payment) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Payment not found',
                ], 404);
            }

            // If already in a terminal state, skip the external API call
            if (in_array($payment->status, ['succeeded', 'failed', 'canceled'])) {
                return response()->json([
                    'status' => 'success',
                    'data'   => [
                        'reference_id' => $referenceId,
                        'status'       => $payment->status,
                        'amount'       => $payment->amount,
                        'currency'     => $payment->currency,
                        'package_id'   => $payment->package_id,
                    ],
                ]);
            }

            $momoData     = $this->momoService->getPaymentStatus($referenceId);
            $mappedStatus = $this->momoService->mapMomoStatus($momoData['status'] ?? 'PENDING');

            if ($mappedStatus !== $payment->status) {
                $payment->update([
                    'status'       => $mappedStatus,
                    'processed_at' => $mappedStatus === 'succeeded' ? now() : $payment->processed_at,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'reference_id' => $referenceId,
                    'status'       => $mappedStatus,
                    'amount'       => $payment->amount,
                    'currency'     => $payment->currency,
                    'package_id'   => $payment->package_id,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('MoMo getStatus error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve payment status',
            ], 500);
        }
    }

    /**
     * Create the delivery package after a successful MoMo escrow payment.
     * Mirrors the Stripe createPackageAfterPayment endpoint.
     *
     * POST /api/momo/create-package
     */
    public function createPackageAfterPayment(Request $request)
    {
        $request->validate([
            'momo_reference_id'            => 'required|string',
            'package_data'                 => 'required|array',
            'package_data.pickup_name'     => 'required|string',
            'package_data.pickup_mobile'   => 'required|string',
            'package_data.pickup_address'  => 'required|string',
            'package_data.weight'          => 'required|numeric|min:0.01',
            'package_data.price'           => 'required|numeric|min:0.01',
            'package_data.pickup_date'     => 'required|date|after_or_equal:today',
            'package_data.pickup_time'     => 'required|date_format:H:i',
            'package_data.drop_name'       => 'required|string',
            'package_data.drop_mobile'     => 'required|string',
            'package_data.drop_address'    => 'required|string',
            'package_data.pickup_address2' => 'nullable|string',
            'package_data.pickup_address3' => 'nullable|string',
            'package_data.pickup_details'  => 'nullable|string',
            'package_data.drop_address2'   => 'nullable|string',
            'package_data.drop_address3'   => 'nullable|string',
            'package_data.drop_details'    => 'nullable|string',
            'package_data.pickup_lat'      => 'nullable|numeric',
            'package_data.pickup_lng'      => 'nullable|numeric',
            'package_data.drop_lat'        => 'nullable|numeric',
            'package_data.drop_lng'        => 'nullable|numeric',
            'package_data.pickup_image'    => 'nullable|string',
        ]);

        try {
            $payment = Payment::where('momo_reference_id', $request->momo_reference_id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$payment) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Payment not found',
                ], 404);
            }

            if ($payment->status !== 'succeeded') {
                return response()->json([
                    'status'  => 'pending',
                    'message' => 'Payment is still being processed. Please wait for mobile approval.',
                ], 202);
            }

            if ($payment->package_id) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Package already exists for this payment',
                ], 400);
            }

            $packageData              = $request->package_data;
            $packageData['sender_id'] = Auth::id();
            $package                  = Package::create($packageData);

            $payment->update(['package_id' => $package->id]);
            $package->load('sender:id,image');

            return response()->json([
                'status'  => 'success',
                'message' => 'Package created successfully',
                'data'    => [
                    'id'     => $package->id,
                    'sender' => [
                        'id'    => $package->sender->id,
                        'image' => $package->sender->image,
                    ],
                    'weight' => $package->weight,
                    'price'  => $package->price,
                    'pickup' => [
                        'name'    => $package->pickup_name,
                        'mobile'  => $package->pickup_mobile,
                        'address' => $package->pickup_address,
                        'address2'=> $package->pickup_address2,
                        'address3'=> $package->pickup_address3,
                        'details' => $package->pickup_details,
                        'date'    => date('Y-m-d', strtotime($package->pickup_date)),
                        'time'    => date('H:i', strtotime($package->pickup_time)),
                        'image'   => $package->pickup_image,
                        'coordinates' => [
                            'lat' => $package->pickup_lat,
                            'lng' => $package->pickup_lng,
                        ],
                    ],
                    'drop' => [
                        'name'    => $package->drop_name,
                        'mobile'  => $package->drop_mobile,
                        'address' => $package->drop_address,
                        'address2'=> $package->drop_address2,
                        'address3'=> $package->drop_address3,
                        'details' => $package->drop_details,
                        'coordinates' => [
                            'lat' => $package->drop_lat,
                            'lng' => $package->drop_lng,
                        ],
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('MoMo createPackageAfterPayment error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to create package after MoMo payment',
            ], 500);
        }
    }

    /**
     * Handle MTN MoMo collection callback (payment notification).
     * This endpoint must be publicly accessible (no auth middleware).
     *
     * POST /api/momo/callback
     */
    public function callback(Request $request)
    {
        Log::info('MoMo collection callback received', [
            'headers' => $request->headers->all(),
            'body'    => $request->all(),
        ]);

        try {
            // MTN sends the reference ID in the X-Reference-Id header or body
            $referenceId = $request->header('X-Reference-Id')
                ?? $request->input('referenceId')
                ?? $request->input('financialTransactionId');

            if (!$referenceId) {
                Log::warning('MoMo callback: missing reference ID');
                return response()->json(['status' => 'ok']);
            }

            $payment = Payment::where('momo_reference_id', $referenceId)
                ->where('payment_type', 'escrow')
                ->first();

            if (!$payment) {
                Log::warning('MoMo callback: payment not found', ['reference_id' => $referenceId]);
                return response()->json(['status' => 'ok']);
            }

            $momoStatus   = $request->input('status', '');
            $mappedStatus = $this->momoService->mapMomoStatus($momoStatus);

            $payment->update([
                'status'       => $mappedStatus,
                'processed_at' => $mappedStatus === 'succeeded' ? now() : $payment->processed_at,
            ]);

            Log::info('MoMo collection callback processed', [
                'reference_id' => $referenceId,
                'momo_status'  => $momoStatus,
                'mapped_status'=> $mappedStatus,
            ]);

        } catch (\Exception $e) {
            Log::error('MoMo collection callback error: ' . $e->getMessage());
        }

        // Always return 200 so MTN does not retry
        return response()->json(['status' => 'ok']);
    }

    /**
     * Disburse funds from the platform wallet to a rider's mobile money account.
     *
     * POST /api/momo/disburse
     */
    public function disburse(Request $request)
    {
        $request->validate([
            'amount'   => 'required|numeric|min:0.01',
            'phone'    => 'required|string',
            'currency' => 'nullable|string|size:3',
        ]);

        try {
            $user        = Auth::user();
            $referenceId = Str::uuid()->toString();
            $currency    = config('services.momo.currency', 'EUR');

            // Calculate available balance: released earnings minus existing withdrawals
            $released = Payment::where('user_id', $user->id)
                ->where('payment_type', 'release')
                ->where('status', 'succeeded')
                ->sum('amount');

            $withdrawn = Payment::where('user_id', $user->id)
                ->where('payment_type', 'withdrawal')
                ->whereIn('status', ['succeeded', 'processing', 'pending'])
                ->sum('amount');

            $availableBalance = $released - $withdrawn;

            if ($request->amount > $availableBalance) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Insufficient available balance',
                ], 400);
            }

            $this->momoService->disburse(
                (float) $request->amount,
                $request->phone,
                $referenceId,
                $currency
            );

            Payment::create([
                'user_id'           => $user->id,
                'payment_gateway'   => 'momo',
                'momo_reference_id' => $referenceId,
                'momo_phone_number' => $request->phone,
                'amount'            => $request->amount,
                'currency'          => $currency,
                'status'            => 'pending',
                'payment_type'      => 'withdrawal',
            ]);

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'reference_id' => $referenceId,
                    'amount'       => $request->amount,
                    'currency'     => $currency,
                    'status'       => 'pending',
                    'message'      => 'MoMo withdrawal initiated. Funds will be sent to your mobile wallet shortly.',
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('MoMo disburse error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to initiate MoMo withdrawal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle MTN MoMo disbursement callback.
     * This endpoint must be publicly accessible (no auth middleware).
     *
     * POST /api/momo/callback/disbursement
     */
    public function disbursementCallback(Request $request)
    {
        Log::info('MoMo disbursement callback received', [
            'headers' => $request->headers->all(),
            'body'    => $request->all(),
        ]);

        try {
            $referenceId = $request->header('X-Reference-Id')
                ?? $request->input('referenceId')
                ?? $request->input('financialTransactionId');

            if (!$referenceId) {
                return response()->json(['status' => 'ok']);
            }

            $payment = Payment::where('momo_reference_id', $referenceId)
                ->where('payment_type', 'withdrawal')
                ->first();

            if (!$payment) {
                return response()->json(['status' => 'ok']);
            }

            $momoStatus   = $request->input('status', '');
            $mappedStatus = $this->momoService->mapMomoStatus($momoStatus);

            $payment->update([
                'status'       => $mappedStatus,
                'processed_at' => $mappedStatus === 'succeeded' ? now() : $payment->processed_at,
            ]);

            Log::info('MoMo disbursement callback processed', [
                'reference_id' => $referenceId,
                'mapped_status'=> $mappedStatus,
            ]);

        } catch (\Exception $e) {
            Log::error('MoMo disbursement callback error: ' . $e->getMessage());
        }

        return response()->json(['status' => 'ok']);
    }
}
