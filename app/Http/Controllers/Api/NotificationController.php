<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Package;
use App\User;

class NotificationController extends Controller
{
    public function index()
    {
        // Mark all user's unread notifications as read when they visit the page
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // Get user's personal notifications (now all marked as read)
        $userNotifications = Notification::where('user_id', Auth::id())
            ->where('is_read', true)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get admin's unread notifications (common for all users)
        $adminNotifications = Notification::where('user_id', 1)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();

        // Combine and format all notifications
        $notifications = $userNotifications->concat($adminNotifications)
            ->sortByDesc('created_at')
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'description' => $notification->description,
                    'date' => $notification->created_at->format('Y-m-d'),
                    'time' => $notification->created_at->format('h:i A'),
                    'created_at_utc' => $notification->created_at->toIso8601String(),
                    'isNew' => !$notification->is_read,
                    'type' => $notification->type,
                    'isAdminNotification' => $notification->user_id === 1
                ];
            });

        return response()->json($notifications);
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Notification marked as read']);
    }

    public function delete($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $notification->delete(); // This will soft delete due to SoftDeletes trait

        return response()->json(['message' => 'Notification deleted successfully']);
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function getUnreadCount(Request $request)
    {
        $user = Auth::user();
        
        // If location is provided and user is a rider, create notifications for nearby packages
        if ($request->has('pickup_lat') && $request->has('pickup_lng') && $user->hasRole("dropper")) {
            try {
                $this->createNearbyPackageNotifications(
                    $user->id,
                    $request->pickup_lat,
                    $request->pickup_lng
                );
            } catch (\Exception $e) {
                // Log error but continue to return count
                \Log::error('Error creating notifications for nearby packages: ' . $e->getMessage());
            }
        }

        // Count user's personal unread notifications
        $userCount = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        // Count admin's unread notifications (common for all users)
        $adminCount = Notification::where('user_id', 1)
            ->where('is_read', false)
            ->count();

        $totalCount = $userCount + $adminCount;

        return response()->json(['count' => $totalCount]);
    }

    // Helper methods for creating different types of notifications
    public static function createOrderNotification($userId, $orderId, $amount)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => 'order',
            'title' => 'New Order',
            'description' => "You have received a new order #{$orderId}",
            'data' => [
                'order_id' => $orderId,
                'amount' => $amount
            ]
        ]);
    }

    public static function createMessageNotification($userId, $messageId, $senderId, $senderName)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => 'message',
            'title' => 'New Message',
            'description' => "You have received a new message from {$senderName}",
            'data' => [
                'message_id' => $messageId,
                'sender_id' => $senderId,
                'sender_name' => $senderName
            ]
        ]);
    }

    public static function createPackageNotification($userId, $packageId, $status)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => 'package',
            'title' => 'Package Update',
            'description' => "Your package #{$packageId} status has been updated to {$status}",
            'data' => [
                'package_id' => $packageId,
                'status' => $status
            ]
        ]);
    }

    public static function createPaymentNotification($userId, $paymentId, $amount)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => 'payment',
            'title' => 'Payment Received',
            'description' => "Payment of ${$amount} has been received",
            'data' => [
                'payment_id' => $paymentId,
                'amount' => $amount
            ]
        ]);
    }

    public static function createSystemNotification($userId, $title, $description, $data = [])
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => 'system',
            'title' => $title,
            'description' => $description,
            'data' => $data
        ]);
    }

    // App Update
    public static function createAppUpdateNotification($userId, $version, $changes)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => 'app_update',
            'title' => 'New App Update Available',
            'description' => "New features have been added to PiqDrop",
            'data' => [
                'version' => $version,
                'changes' => $changes
            ]
        ]);
    }

    // Maintenance
    public static function createMaintenanceNotification($userId, $date, $duration, $services)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => 'maintenance',
            'title' => 'Scheduled Maintenance',
            'description' => "System maintenance scheduled for {$date}",
            'data' => [
                'date' => $date,
                'duration' => $duration,
                'affected_services' => $services
            ]
        ]);
    }

    // New Order Received
    public static function createOrderReceivedNotification($userId, $orderId, $amount)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => 'order_received',
            'title' => 'New Order Received',
            'description' => "You have received a new order #{$orderId}",
            'data' => [
                'order_id' => $orderId,
                'amount' => $amount
            ]
        ]);
    }

    // Order Status Update
    public static function createOrderStatusUpdateNotification($userId, $orderId, $status, $trackingNumber)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => 'order_status',
            'title' => 'Order Status Updated',
            'description' => "Your order #{$orderId} is now {$status}",
            'data' => [
                'order_id' => $orderId,
                'status' => $status,
                'tracking_number' => $trackingNumber
            ]
        ]);
    }

    // Package Created
    public static function createPackageCreatedNotification($userId, $packageId, $pickupDate)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => 'package_created',
            'title' => 'New Package Created',
            'description' => "Your package has been created successfully",
            'data' => [
                'package_id' => $packageId,
                'pickup_date' => $pickupDate
            ]
        ]);
    }

    // Package Status Update
    public static function createPackageStatusUpdateNotification($userId, $packageId, $status, $location)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => 'package_status',
            'title' => 'Package Status Update',
            'description' => "Your package is now {$status}",
            'data' => [
                'package_id' => $packageId,
                'status' => $status,
                'location' => $location
            ]
        ]);
    }

    // Payment Received
    public static function createPaymentReceivedNotification($userId, $paymentId, $amount, $paymentMethod)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => 'payment_received',
            'title' => 'Payment Received',
            'description' => "Payment of ${$amount} has been received",
            'data' => [
                'payment_id' => $paymentId,
                'amount' => $amount,
                'method' => $paymentMethod
            ]
        ]);
    }

    // Payment Failed
    public static function createPaymentFailedNotification($userId, $paymentId, $amount, $failureReason)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => 'payment_failed',
            'title' => 'Payment Failed',
            'description' => "Your payment of ${$amount} has failed",
            'data' => [
                'payment_id' => $paymentId,
                'amount' => $amount,
                'reason' => $failureReason
            ]
        ]);
    }

    // Profile Update
    public static function createProfileUpdateNotification($userId, $updatedFields)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => 'profile_update',
            'title' => 'Profile Updated',
            'description' => "Your profile has been successfully updated",
            'data' => [
                'updated_fields' => $updatedFields
            ]
        ]);
    }

    // Account Verification
    public static function createAccountVerifiedNotification($userId, $date)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => 'account_verified',
            'title' => 'Account Verified',
            'description' => "Your account has been successfully verified",
            'data' => [
                'verification_date' => $date
            ]
        ]);
    }

    // Delivery Scheduled
    public static function createDeliveryScheduledNotification($userId, $deliveryId, $date, $timeSlot)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => 'delivery_scheduled',
            'title' => 'Delivery Scheduled',
            'description' => "Your delivery has been scheduled for {$date}",
            'data' => [
                'delivery_id' => $deliveryId,
                'date' => $date,
                'time_slot' => $timeSlot
            ]
        ]);
    }

    // Delivery Status
    public static function createDeliveryStatusNotification($userId, $deliveryId, $status, $estimatedTime)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => 'delivery_status',
            'title' => 'Delivery Status Update',
            'description' => "Your order is now {$status}." . ($status == 'completed' ? " Accept delivery!" : ""),
            'data' => [
                'delivery_id' => $deliveryId,
                'status' => $status,
                'estimated_time' => $estimatedTime
            ]
        ]);
    }

    // Package Picked Up
    public static function createPickupStatusNotification($userId, $deliveryId, $pickupTime)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => 'pickup_status',
            'title' => 'Package Picked Up',
            'description' => 'Your package has been picked up by the rider.',
            'data' => [
                'delivery_id' => $deliveryId,
                'pickup_time' => $pickupTime,
            ],
        ]);
    }

    // Special Offer
    public static function createSpecialOfferNotification($userId, $promoCode, $validUntil, $discount)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => 'promotion',
            'title' => 'Special Offer',
            'description' => "Get 20% off on your next delivery",
            'data' => [
                'promo_code' => $promoCode,
                'valid_until' => $validUntil,
                'discount' => $discount
            ]
        ]);
    }

    // Loyalty Points
    public static function createLoyaltyPointsNotification($userId, $points, $totalPoints, $expiryDate)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => 'loyalty_points',
            'title' => 'Loyalty Points Added',
            'description' => "You've earned {$points} loyalty points",
            'data' => [
                'points' => $points,
                'total_points' => $totalPoints,
                'expiry_date' => $expiryDate
            ]
        ]);
    }

    // Package Available for Riders
    public static function createPackageAvailableNotification($userId, $packageId, $price, $pickupDistance, $dropoffDistance, $pickupAddress, $dropoffAddress)
    {
        // Ensure distances are properly rounded to 2 decimal places
        $pickupDistance = round($pickupDistance, 2);
        $dropoffDistance = $dropoffDistance ? round($dropoffDistance, 2) : null;
        
        $description = $dropoffDistance 
            ? "Package nearby - $" . $price . " (Pickup: " . $pickupDistance . "km, Dropoff: " . $dropoffDistance . "km)"
            : "Package nearby - $" . $price . " (Distance: " . $pickupDistance . "km)";
            
        return Notification::create([
            'user_id' => $userId,
            'type' => 'package_available',
            'title' => 'New Package Available',
            'description' => $description,
            'data' => [
                'package_id' => $packageId,
                'price' => $price,
                'pickup_distance' => $pickupDistance,
                'dropoff_distance' => $dropoffDistance,
                'pickup_address' => $pickupAddress,
                'dropoff_address' => $dropoffAddress
            ]
        ]);
    }

    /**
     * Sync nearby packages and create notifications for rider
     * Called when rider opens notification page
     * Uses rider's current location (similar to getPackagesByRiderLocation in index.tsx)
     */
    public function syncNearbyPackages(Request $request)
    {
        $user = Auth::user();
        
        // Check if user is a rider
        if (!$user->hasRole("dropper")) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only droppers can sync packages'
            ], 403);
        }

        // Validate request has current location
        $request->validate([
            'pickup_lat' => 'required|numeric',
            'pickup_lng' => 'required|numeric',
        ]);

        try {
            // Create notifications for nearby packages using common method
            $newNotifications = $this->createNearbyPackageNotifications(
                $user->id,
                $request->pickup_lat,
                $request->pickup_lng
            );

            // Mark all user's unread notifications as read when they visit the page (same logic as index method)
            Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            // Get user's personal notifications (now all marked as read) - same logic as index method
            $userNotifications = Notification::where('user_id', $user->id)
                ->where('is_read', true)
                ->orderBy('created_at', 'desc')
                ->get();

            // Get admin's unread notifications (common for all users)
            $adminNotifications = Notification::where('user_id', 1)
                ->where('is_read', false)
                ->orderBy('created_at', 'desc')
                ->get();

            // Combine and format all notifications
            $allNotifications = collect(); // Initialize as empty collection
            
            if ($userNotifications || $adminNotifications) {
                $allNotifications = $userNotifications->concat($adminNotifications)
                    ->sortByDesc('created_at')
                    ->map(function ($notification) {
                        return [
                            'id' => $notification->id,
                            'title' => $notification->title,
                            'description' => $notification->description,
                            'date' => $notification->created_at->format('Y-m-d'),
                            'time' => $notification->created_at->format('h:i A'),
                            'created_at_utc' => $notification->created_at->toIso8601String(),
                            'isNew' => !$notification->is_read,
                            'type' => $notification->type,
                            'isAdminNotification' => $notification->user_id === 1
                        ];
                    })
                    ->values();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Package sync completed',
                'new_notifications' => $newNotifications,
                'notifications' => $allNotifications->toArray() // Ensure it's an array
            ]);

        } catch (\Exception $e) {
            // Log the full error for debugging
            \Log::error('Sync packages error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error syncing packages: ' . $e->getMessage(),
                'debug' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ] : null
            ], 500);
        }
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     * Returns distance in meters
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    /**
     * Find and create notifications for nearby packages
     * Returns the number of new notifications created
     * 
     * @param int $userId The rider's user ID
     * @param float $riderLat The rider's latitude
     * @param float $riderLng The rider's longitude
     * @return int Number of new notifications created
     */
    private function createNearbyPackageNotifications($userId, $riderLat, $riderLng)
    {
        $radius = 1000; // 100km radius (same as in PackageController searchPackages)
        $radiusInMeters = $radius * 1000;

        // Get existing package notifications to avoid duplicates
        $existingPackageIds = Notification::where('user_id', $userId)
            ->where('type', 'package_available')
            ->get()
            ->pluck('data')
            ->map(function($data) {
                // Check if data is already an array (Laravel auto-decodes JSON columns)
                $decoded = is_array($data) ? $data : json_decode($data, true);
                return $decoded['package_id'] ?? null;
            })
            ->filter()
            ->toArray();

        // Find nearby packages using the same logic as PackageController searchPackages
        $packages = Package::select('*')
            ->selectRaw('
                ( 6371000 * acos( cos( radians(?) ) *
                    cos( radians( pickup_lat ) ) *
                    cos( radians( pickup_lng ) - radians(?) ) +
                    sin( radians(?) ) *
                    sin( radians( pickup_lat ) )
                ) ) AS pickup_distance', 
                [$riderLat, $riderLng, $riderLat]
            )
            ->whereRaw('
                ( 6371000 * acos( cos( radians(?) ) *
                    cos( radians( pickup_lat ) ) *
                    cos( radians( pickup_lng ) - radians(?) ) +
                    sin( radians(?) ) *
                    sin( radians( pickup_lat ) )
                ) ) <= ?', 
                [$riderLat, $riderLng, $riderLat, $radiusInMeters]
            )
            ->where('status', 'active')
            ->whereDoesntHave('order', function($query) {
                $query->whereIn('status', ['active', 'completed']);
            })
            ->where('pickup_date', '>=', date('Y-m-d'))
            ->whereNotIn('id', $existingPackageIds) // Exclude packages that already have notifications
            ->with('sender:id,image,first_name,last_name,mobile')
            ->orderBy('pickup_date', 'asc')
            ->orderBy('pickup_distance')
            ->get();

        $newNotifications = 0;

        // Create notifications for new nearby packages
        if ($packages && $packages->count() > 0) {
            foreach ($packages as $package) {
                $pickupDistanceKm = round($package->pickup_distance / 1000, 2);
                
                self::createPackageAvailableNotification(
                    $userId,
                    $package->id,
                    $package->price,
                    $pickupDistanceKm,
                    null,
                    $package->pickup_address,
                    $package->drop_address
                );
                $newNotifications++;
            }
        }

        return $newNotifications;
    }
} 