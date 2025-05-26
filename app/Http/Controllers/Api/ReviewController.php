<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    /**
     * Store a newly created review in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => [
                'required',
                'exists:orders,id',
                function($attribute, $value, $fail) {
                    $reviewCount = Review::where('order_id', $value)->count();
                    if ($reviewCount >= 2) {
                        $fail('This order already has the maximum number of reviews (2).');
                    }
                },
            ],
            'reviewee_id' => 'required|exists:users,id|different:reviewer_id',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|max:255',
        ]);

        // Set reviewer_id as the authenticated user
        $data = $request->all();
        $data['reviewer_id'] = auth()->id();

        // Check if the authenticated user is either the sender or dropper of the order
        $order = Order::with('package')->findOrFail($request->order_id);
        $userId = (int)auth()->id();
        $senderId = (int)$order->package->sender_id;
        $dropperId = (int)$order->dropper_id;
        $revieweeId = (int)$request->reviewee_id;
        
        // Check if order is completed
        if ($order->status !== 'completed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Reviews can only be submitted for completed orders'
            ], 422);
        }

        if ($userId !== $senderId && $userId !== $dropperId) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to review this order'
            ], 403);
        }

        // If user is sender, they can only review dropper
        if ($userId === $senderId && $revieweeId !== $dropperId) {
            return response()->json([
                'status' => 'error',
                'message' => 'As a sender, you can only review the dropper'
            ], 422);
        }

        // If user is dropper, they can only review sender
        if ($userId === $dropperId && $revieweeId !== $senderId) {
            return response()->json([
                'status' => 'error',
                'message' => 'As a dropper, you can only review the sender'
            ], 422);
        }

        // Check if the user has already submitted a review for this order
        $existingReview = Review::where('order_id', $request->order_id)
            ->where('reviewer_id', $userId)
            ->first();

        if ($existingReview) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have already submitted a review for this order'
            ], 422);
        }

        $review = Review::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Review submitted successfully',
            'data' => $review
        ], 201);
    }
} 