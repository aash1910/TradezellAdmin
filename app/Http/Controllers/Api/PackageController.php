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
            'drop_lat' => 'nullable|numeric',
            'drop_lng' => 'nullable|numeric',
            'pickup_date' => 'nullable|date_format:Y-m-d',
            'pickup_time' => 'nullable|date_format:H:i',
            'radius' => 'sometimes|numeric|min:1|max:300', // radius in kilometers, default 100km
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
            ->when($request->has(['drop_lat', 'drop_lng']), function($query) use ($request) {
                return $query->selectRaw('
                    ( 6371000 * acos( cos( radians(?) ) *
                        cos( radians( drop_lat ) ) *
                        cos( radians( drop_lng ) - radians(?) ) +
                        sin( radians(?) ) *
                        sin( radians( drop_lat ) )
                    ) ) AS drop_distance', 
                    [$request->drop_lat, $request->drop_lng, $request->drop_lat]
                );
            })
            ->whereRaw('
                ( 6371000 * acos( cos( radians(?) ) *
                    cos( radians( pickup_lat ) ) *
                    cos( radians( pickup_lng ) - radians(?) ) +
                    sin( radians(?) ) *
                    sin( radians( pickup_lat ) )
                ) ) <= ?', 
                [$request->pickup_lat, $request->pickup_lng, $request->pickup_lat, $radiusInMeters]
            )
            ->when($request->has(['drop_lat', 'drop_lng']), function($query) use ($request, $radiusInMeters) {
                return $query->whereRaw('
                    ( 6371000 * acos( cos( radians(?) ) *
                        cos( radians( drop_lat ) ) *
                        cos( radians( drop_lng ) - radians(?) ) +
                        sin( radians(?) ) *
                        sin( radians( drop_lat ) )
                    ) ) <= ?', 
                    [$request->drop_lat, $request->drop_lng, $request->drop_lat, $radiusInMeters]
                );
            })
            ->when($request->pickup_date, function($query) use ($request) {
                return $query->where('pickup_date', $request->pickup_date);
            }, function($query) {
                // If no pickup_date provided, get packages from today onwards
                return $query->where('pickup_date', '>=', date('Y-m-d'));
            })
            ->when($request->pickup_time, function($query) use ($request) {
                return $query->where('pickup_time', $request->pickup_time);
            })
            ->where('status', 'active')
            ->whereDoesntHave('order', function($query) {
                $query->whereIn('status', ['active', 'completed']);
            })
            ->with('sender:id,image,first_name,last_name,mobile')
            ->orderBy('pickup_date', 'asc')
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
                    'drop_distance' => isset($package->drop_distance) ? round($package->drop_distance / 1000, 2) : null, // Convert to kilometers if exists
                    'sender' => [
                        'id' => $package->sender->id,
                        'image' => $package->sender->image,
                        'name' => $package->sender->first_name . ' ' . $package->sender->last_name,
                        'mobile' => $package->sender->mobile,
                    ],
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
     * Upload pickup image for a package
     * @author Ashraful Islam
     */
    public function uploadPickupImage(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'package_id' => 'required|exists:packages,id',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $package = Package::where('id', $request->package_id)
            ->where('sender_id', $user->id)
            ->first();

        if (!$package) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized or package not found',
            ], 403);
        }

        $file = $request->file('image');
        $extension = $file->getClientOriginalExtension();
        $image = \Intervention\Image\Facades\Image::make($file);
        $image->resize(800, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $base64Image = 'data:image/' . $extension . ';base64,' . base64_encode($image->encode($extension, 90)->encoded);

        $package->pickup_image = $base64Image;
        $package->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Pickup image uploaded successfully',
            'data' => [
                'pickup_image' => $package->pickup_image,
            ]
        ]);
    }
} 