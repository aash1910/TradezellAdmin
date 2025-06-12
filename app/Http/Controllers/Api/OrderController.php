<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OrderStoreRequest;
use App\Http\Requests\Api\OrderStatusUpdateRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Store a newly created order in storage.
     *
     * @param OrderStoreRequest $request
     * @return JsonResponse
     */
    public function store(OrderStoreRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        
        // Always set dropper_id to the authenticated user
        $validatedData['dropper_id'] = auth()->id();
        
        // Set default status to ongoing if not provided
        $validatedData['status'] = $validatedData['status'] ?? 'ongoing';
        
        $order = Order::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Order created successfully',
            'data' => [
                'id' => $order->id,
                'package' => [
                    'id' => $order->package->id,
                    'info' => $order->package->package_info,
                ],
                'dropper' => [
                    'id' => $order->dropper->id,
                    'name' => $order->dropper->full_name,
                ],
                'status' => $order->status,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ]
        ], 201);
    }

    /**
     * Update the order status.
     *
     * @param OrderStatusUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus(OrderStatusUpdateRequest $request, $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $oldStatus = $order->status;
        
        $order->update([
            'status' => $request->status
        ]); 
        
        if ($order->status === 'completed') {
            $order->package->update([
                'status' => 'delivered'
            ]);
        }

        if ($order->status === 'canceled') {
            $order->package->update([
                'status' => 'inactive'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Order status updated successfully',
            'data' => [
                'id' => $order->id,
                'package' => [
                    'id' => $order->package->id,
                    'info' => $order->package->package_info,
                ],
                'dropper' => [
                    'id' => $order->dropper->id,
                    'name' => $order->dropper->full_name,
                ],
                'status' => [
                    'old' => $oldStatus,
                    'new' => $order->status
                ],
                'updated_at' => $order->updated_at,
            ]
        ]);
    }

    /**
     * Get authenticated dropper's order list with package details
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function myOrders(Request $request): JsonResponse
    {
        // Ensure user is a dropper
        if (!auth()->user()->hasRole('dropper')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Only droppers can access their order list.',
            ], 403);
        }

        $orders = Order::with(['package' => function($query) {
                $query->with('sender:id,image,first_name,last_name,mobile'); // Include sender details
            }])
            ->where('dropper_id', auth()->id())
            ->latest()
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->package->id,
                    'info' => $order->package->package_info,
                    'weight' => $order->package->weight,
                    'price' => $order->package->price,
                    'status' => $order->package->status,
                    'sender' => [
                        'id' => $order->package->sender->id,
                        'name' => $order->package->sender->first_name . ' ' . $order->package->sender->last_name,
                        'mobile' => $order->package->sender->mobile,
                        'image' => $order->package->sender->image,
                    ],
                    'pickup' => [
                        'name' => $order->package->pickup_name,
                        'mobile' => $order->package->pickup_mobile,
                        'address' => $order->package->pickup_address,
                        'details' => $order->package->pickup_details,
                        'date' => date('Y-m-d', strtotime($order->package->pickup_date)),
                        'time' => date('H:i', strtotime($order->package->pickup_time)),
                        'coordinates' => [
                            'lat' => $order->package->pickup_lat,
                            'lng' => $order->package->pickup_lng,
                        ],
                    ],
                    'drop' => [
                        'name' => $order->package->drop_name,
                        'mobile' => $order->package->drop_mobile,
                        'address' => $order->package->drop_address,
                        'details' => $order->package->drop_details,
                        'coordinates' => [
                            'lat' => $order->package->drop_lat,
                            'lng' => $order->package->drop_lng,
                        ],
                    ],
                    'order' => [
                        'id' => $order->id,
                        'status' => $order->status,
                        'dropper' => [
                            'id' => $order->dropper->id,
                            'name' => $order->dropper->first_name . ' ' . $order->dropper->last_name,
                            'image' => $order->dropper->image,
                            'mobile' => $order->dropper->mobile,
                        ],
                        'review_submitted' => $order->review ? true : false,
                        'created_at' => $order->created_at,
                        'updated_at' => $order->updated_at,
                    ],
                    'created_at' => $order->package->created_at,
                    'updated_at' => $order->package->updated_at,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }
} 