<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PackageStoreRequest;
use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    /**
     * Get authenticated sender's package list with order details
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function myPackages(Request $request): JsonResponse
    {
        $packages = Package::with('order')
            ->where('sender_id', auth()->id())
            ->with('sender:id,image')
            ->with('order.review')
            ->latest()
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'info' => $package->package_info,
                    'weight' => $package->weight,
                    'price' => $package->price,
                    'status' => $package->status,
                    'sender' => [
                        'id' => $package->sender->id,
                        'image' => $package->sender->image,
                    ],
                    'pickup' => [
                        'name' => $package->pickup_name,
                        'mobile' => $package->pickup_mobile,
                        'address' => $package->pickup_address,
                        'details' => $package->pickup_details,
                        'date' => date('Y-m-d', strtotime($package->pickup_date)),
                        'time' => date('H:i', strtotime($package->pickup_time)),
                        'coordinates' => [
                            'lat' => $package->pickup_lat,
                            'lng' => $package->pickup_lng,
                        ],
                    ],
                    'drop' => [
                        'name' => $package->drop_name,
                        'mobile' => $package->drop_mobile,
                        'address' => $package->drop_address,
                        'details' => $package->drop_details,
                        'coordinates' => [
                            'lat' => $package->drop_lat,
                            'lng' => $package->drop_lng,
                        ],
                    ],
                    'order' => $package->order ? [
                        'id' => $package->order->id,
                        'status' => $package->order->status,
                        'dropper' => [
                            'id' => $package->order->dropper->id,
                            'name' => $package->order->dropper->first_name . ' ' . $package->order->dropper->last_name,
                            'image' => $package->order->dropper->image,
                            'mobile' => $package->order->dropper->mobile,
                        ],
                        'review_submitted' => $package->order->review ? true : false,
                        'created_at' => $package->order->created_at,
                        'updated_at' => $package->order->updated_at,
                    ] : [
                        'status' => $package->status === 'active' ? 'ongoing' : 'canceled'
                    ],
                    'created_at' => $package->created_at,
                    'updated_at' => $package->updated_at,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $packages
        ]);
    }

    /**
     * Cancel a package
     *
     * @param int $id
     * @return JsonResponse
     */
    public function cancel($id): JsonResponse
    {
        $package = Package::with('order')->findOrFail($id);

        // Check if the authenticated user owns this package
        if ($package->sender_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to cancel this package'
            ], 403);
        }

        // Check if package can be cancelled
        if ($package->status === 'delivered') {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot cancel a delivered package'
            ], 422);
        }

        if ($package->order && in_array($package->order->status, ['completed', 'active'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot cancel a package that has an active or completed order'
            ], 422);
        }

        // Cancel the package
        $package->update(['status' => 'inactive']);

        // If there's an ongoing order, cancel it too
        if ($package->order && $package->order->status === 'ongoing') {
            $package->order->update(['status' => 'canceled']);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Package cancelled successfully',
            'data' => [
                'id' => $package->id,
                'status' => $package->status,
                'order_status' => $package->order ? $package->order->status : null
            ]
        ]);
    }

    /**
     * Store a newly created package in storage.
     *
     * @param PackageStoreRequest $request
     * @return JsonResponse
     */
    public function store(PackageStoreRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $validatedData['sender_id'] = auth()->id();

        $package = Package::create($validatedData);
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
                    'details' => $package->pickup_details,
                    'date' => date('Y-m-d', strtotime($package->pickup_date)),
                    'time' => date('H:i', strtotime($package->pickup_time)),
                    'coordinates' => [
                        'lat' => $package->pickup_lat,
                        'lng' => $package->pickup_lng,
                    ],
                ],
                'drop' => [
                    'name' => $package->drop_name,
                    'mobile' => $package->drop_mobile,
                    'address' => $package->drop_address,
                    'details' => $package->drop_details,
                    'coordinates' => [
                        'lat' => $package->drop_lat,
                        'lng' => $package->drop_lng,
                    ],
                ]

            ]
        ], 201);
    }

    /**
     * Update the specified package in storage.
     *
     * @param PackageStoreRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(PackageStoreRequest $request, $id): JsonResponse
    {
        $package = Package::findOrFail($id);
        
        // Check if the authenticated user owns this package
        if ($package->sender_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to update this package'
            ], 403);
        }

        $validatedData = $request->validated();
        $package->update($validatedData);
        $package->load('sender:id,image');

        return response()->json([
            'status' => 'success',
            'message' => 'Package updated successfully',
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
                    'details' => $package->pickup_details,
                    'date' => date('Y-m-d', strtotime($package->pickup_date)),
                    'time' => date('H:i', strtotime($package->pickup_time)),
                    'coordinates' => [
                        'lat' => $package->pickup_lat,
                        'lng' => $package->pickup_lng,
                    ],
                ],
                'drop' => [
                    'name' => $package->drop_name,
                    'mobile' => $package->drop_mobile,
                    'address' => $package->drop_address,
                    'details' => $package->drop_details,
                    'coordinates' => [
                        'lat' => $package->drop_lat,
                        'lng' => $package->drop_lng,
                    ],
                ]
            ]
        ]);
    }

    /**
     * Search for packages based on location and pickup schedule
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function searchPackages(Request $request): JsonResponse
    {
        $request->validate([
            'pickup_lat' => 'required|numeric',
            'pickup_lng' => 'required|numeric',
            'drop_lat' => 'required|numeric',
            'drop_lng' => 'required|numeric',
            'pickup_date' => 'required|date_format:Y-m-d',
            'pickup_time' => 'required|date_format:H:i',
            'radius' => 'sometimes|numeric|min:1|max:100', // radius in kilometers, default 100km
        ]);

        $radius = $request->input('radius', 100); // Default radius 100km
        $radiusInMeters = $radius * 1000;

        $packages = Package::select('*')
            ->selectRaw('
                ( 6371000 * acos( cos( radians(?) ) *
                    cos( radians( pickup_lat ) ) *
                    cos( radians( pickup_lng ) - radians(?) ) +
                    sin( radians(?) ) *
                    sin( radians( pickup_lat ) )
                ) ) AS pickup_distance', 
                [$request->pickup_lat, $request->pickup_lng, $request->pickup_lat]
            )
            ->selectRaw('
                ( 6371000 * acos( cos( radians(?) ) *
                    cos( radians( drop_lat ) ) *
                    cos( radians( drop_lng ) - radians(?) ) +
                    sin( radians(?) ) *
                    sin( radians( drop_lat ) )
                ) ) AS drop_distance', 
                [$request->drop_lat, $request->drop_lng, $request->drop_lat]
            )
            ->whereRaw('
                ( 6371000 * acos( cos( radians(?) ) *
                    cos( radians( pickup_lat ) ) *
                    cos( radians( pickup_lng ) - radians(?) ) +
                    sin( radians(?) ) *
                    sin( radians( pickup_lat ) )
                ) ) <= ?', 
                [$request->pickup_lat, $request->pickup_lng, $request->pickup_lat, $radiusInMeters]
            )
            ->whereRaw('
                ( 6371000 * acos( cos( radians(?) ) *
                    cos( radians( drop_lat ) ) *
                    cos( radians( drop_lng ) - radians(?) ) +
                    sin( radians(?) ) *
                    sin( radians( drop_lat ) )
                ) ) <= ?', 
                [$request->drop_lat, $request->drop_lng, $request->drop_lat, $radiusInMeters]
            )
            ->where('pickup_date', $request->pickup_date)
            ->where('pickup_time', $request->pickup_time)
            ->where('status', 'active')
            ->whereDoesntHave('order', function($query) {
                $query->whereIn('status', ['active', 'completed']);
            })
            ->with('sender:id,image')
            ->orderBy('pickup_distance')
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'info' => $package->package_info,
                    'weight' => $package->weight,
                    'price' => $package->price,
                    'status' => $package->status,
                    'pickup_distance' => round($package->pickup_distance / 1000, 2), // Convert to kilometers
                    'drop_distance' => round($package->drop_distance / 1000, 2), // Convert to kilometers
                    'sender' => [
                        'id' => $package->sender->id,
                        'image' => $package->sender->image,
                    ],
                    'pickup' => [
                        'name' => $package->pickup_name,
                        'mobile' => $package->pickup_mobile,
                        'address' => $package->pickup_address,
                        'details' => $package->pickup_details,
                        'date' => $package->pickup_date,
                        'time' => $package->pickup_time,
                        'coordinates' => [
                            'lat' => $package->pickup_lat,
                            'lng' => $package->pickup_lng,
                        ],
                    ],
                    'drop' => [
                        'name' => $package->drop_name,
                        'mobile' => $package->drop_mobile,
                        'address' => $package->drop_address,
                        'details' => $package->drop_details,
                        'coordinates' => [
                            'lat' => $package->drop_lat,
                            'lng' => $package->drop_lng,
                        ],
                    ],
                    'created_at' => $package->created_at,
                    'updated_at' => $package->updated_at,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $packages
        ]);
    }
} 