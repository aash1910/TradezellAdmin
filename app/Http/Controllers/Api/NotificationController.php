<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        // Get user's personal notifications
        $userNotifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        // Get admin's notifications (common for all users)
        $adminNotifications = Notification::where('user_id', 1)
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

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function getUnreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
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
            'description' => "Your delivery is now {$status}",
            'data' => [
                'delivery_id' => $deliveryId,
                'status' => $status,
                'estimated_time' => $estimatedTime
            ]
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
} 