<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingSwipe;
use App\Models\TradezellMatch;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ListingController extends Controller
{
    // ── Discovery feed ────────────────────────────────────────────────────────

    /**
     * GET /listings/feed
     *
     * Query params:
     *   lat, lng, radius_km (default 250), type (trade|sell|both), category, condition
     */
    public function feed(Request $request)
    {
        $user = $request->user();

        // IDs of listings this user already swiped
        $swipedIds = ListingSwipe::where('user_id', $user->id)
            ->pluck('listing_id')
            ->toArray();

        $query = Listing::with('user:id,first_name,last_name,image,settings')
            ->where('status', 'active')
            ->where('user_id', '!=', $user->id)
            ->whereNotIn('id', $swipedIds);

        // Type filter
        if ($request->filled('type') && in_array($request->type, ['trade', 'sell'])) {
            $query->where(function ($q) use ($request) {
                $q->where('type', $request->type)->orWhere('type', 'both');
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Condition filter
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        // Distance filter using Haversine formula (if lat/lng provided)
        $lat = $request->filled('lat') ? (float) $request->lat : null;
        $lng = $request->filled('lng') ? (float) $request->lng : null;
        $radiusKm = $request->filled('radius_km') ? (int) $request->radius_km : 250;

        if ($lat !== null && $lng !== null) {
            // Check if global_search is OFF (default to respecting radius)
            $settings = $this->getUserSettings($user);
            $globalSearch = $settings['global_search'] ?? false;

            if (!$globalSearch) {
                $query->whereRaw(
                    '(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) <= ?',
                    [$lat, $lng, $lat, $radiusKm]
                );
            }
        }

        $listings = $query->orderByDesc('created_at')->paginate(20);

        return response()->json([
            'status'   => 'success',
            'listings' => $listings,
        ]);
    }

    // ── My listings ───────────────────────────────────────────────────────────

    public function myListings(Request $request)
    {
        $listings = Listing::where('user_id', $request->user()->id)
            ->withTrashed()
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'status'   => 'success',
            'listings' => $listings,
        ]);
    }

    // ── Single listing ────────────────────────────────────────────────────────

    public function show(Request $request, $id)
    {
        $listing = Listing::with('user:id,first_name,last_name,image')->findOrFail($id);

        return response()->json([
            'status'  => 'success',
            'listing' => $listing,
        ]);
    }

    // ── Create listing ────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type'        => 'required|in:trade,sell,both',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'condition'   => 'nullable|string|in:new,like_new,good,fair,poor',
            'category'    => 'nullable|string|max:100',
            'price'       => 'nullable|numeric|min:0',
            'currency'    => 'nullable|string|max:10',
            'images'      => 'nullable|array',
            'images.*'    => 'nullable|string',
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $listing = Listing::create([
            'user_id'     => $request->user()->id,
            'type'        => $request->type,
            'title'       => $request->title,
            'description' => $request->description,
            'condition'   => $request->condition,
            'category'    => $request->category,
            'price'       => ($request->type === 'trade') ? null : $request->price,
            'currency'    => $request->currency ?? 'USD',
            'images'      => $request->images ?? [],
            'lat'         => $request->lat,
            'lng'         => $request->lng,
            'status'      => 'active',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Listing created successfully',
            'listing' => $listing,
        ], 201);
    }

    // ── Update listing ────────────────────────────────────────────────────────

    public function update(Request $request, $id)
    {
        $listing = Listing::where('user_id', $request->user()->id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'type'        => 'sometimes|in:trade,sell,both',
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'condition'   => 'nullable|string|in:new,like_new,good,fair,poor',
            'category'    => 'nullable|string|max:100',
            'price'       => 'nullable|numeric|min:0',
            'currency'    => 'nullable|string|max:10',
            'images'      => 'nullable|array',
            'images.*'    => 'nullable|string',
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $listing->update($request->only([
            'type', 'title', 'description', 'condition', 'category',
            'price', 'currency', 'images', 'lat', 'lng',
        ]));

        return response()->json([
            'status'  => 'success',
            'message' => 'Listing updated successfully',
            'listing' => $listing->fresh(),
        ]);
    }

    // ── Delete listing ────────────────────────────────────────────────────────

    public function destroy(Request $request, $id)
    {
        $listing = Listing::where('user_id', $request->user()->id)->findOrFail($id);
        $listing->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Listing deleted successfully',
        ]);
    }

    // ── Update status ─────────────────────────────────────────────────────────

    public function updateStatus(Request $request, $id)
    {
        $listing = Listing::where('user_id', $request->user()->id)->findOrFail($id);

        $request->validate(['status' => 'required|in:active,paused,sold,traded']);
        $listing->update(['status' => $request->status]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Listing status updated',
            'listing' => $listing,
        ]);
    }

    // ── Swipe ─────────────────────────────────────────────────────────────────

    /**
     * POST /listings/{id}/swipe
     * Body: { "direction": "yes" | "no" }
     *
     * Returns { matched: bool, match?: { id, other_user, ... } }
     */
    public function swipe(Request $request, $id)
    {
        $request->validate([
            'direction' => 'required|in:yes,no',
        ]);

        $swiper  = $request->user();
        $listing = Listing::where('status', 'active')
            ->where('user_id', '!=', $swiper->id)
            ->findOrFail($id);

        $owner = $listing->user;

        // Record the swipe (upsert so retries are idempotent)
        ListingSwipe::updateOrCreate(
            ['user_id' => $swiper->id, 'listing_id' => $listing->id],
            ['direction' => $request->direction, 'owner_id' => $owner->id]
        );

        if ($request->direction === 'no') {
            return response()->json(['status' => 'success', 'matched' => false]);
        }

        // Check for mutual match: has the owner already swiped yes on ANY of swiper's listings?
        $existingMatch = TradezellMatch::where(function ($q) use ($swiper, $owner) {
            $q->where('user_one_id', $swiper->id)->where('user_two_id', $owner->id);
        })->orWhere(function ($q) use ($swiper, $owner) {
            $q->where('user_one_id', $owner->id)->where('user_two_id', $swiper->id);
        })->where('status', 'active')->first();

        if ($existingMatch) {
            // Match already exists
            return response()->json([
                'status'  => 'success',
                'matched' => true,
                'match'   => $this->formatMatch($existingMatch, $swiper->id),
            ]);
        }

        $ownerLikedSwiper = ListingSwipe::where('user_id', $owner->id)
            ->where('owner_id', $swiper->id)
            ->where('direction', 'yes')
            ->exists();

        if (!$ownerLikedSwiper) {
            return response()->json(['status' => 'success', 'matched' => false]);
        }

        // Mutual interest — create match inside a transaction
        $match = DB::transaction(function () use ($swiper, $owner) {
            $newMatch = TradezellMatch::create([
                'user_one_id' => min($swiper->id, $owner->id),
                'user_two_id' => max($swiper->id, $owner->id),
                'status'      => 'active',
            ]);

            // Notify both users via notifications table
            $this->createMatchNotification($swiper->id, $owner->id, $newMatch->id);
            $this->createMatchNotification($owner->id, $swiper->id, $newMatch->id);

            return $newMatch;
        });

        return response()->json([
            'status'  => 'success',
            'matched' => true,
            'match'   => $this->formatMatch($match, $swiper->id),
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function getUserSettings(User $user): array
    {
        if (!$user->settings) {
            return [];
        }
        $settings = is_array($user->settings)
            ? $user->settings
            : json_decode($user->settings, true);

        return $settings ?? [];
    }

    private function formatMatch(TradezellMatch $match, int $currentUserId): array
    {
        $other = ($match->user_one_id === $currentUserId)
            ? $match->userTwo
            : $match->userOne;

        return [
            'id'             => $match->id,
            'status'         => $match->status,
            'created_at'     => $match->created_at,
            'other_user'     => $other ? [
                'id'         => $other->id,
                'first_name' => $other->first_name,
                'last_name'  => $other->last_name,
                'image'      => $other->image,
            ] : null,
        ];
    }

    private function createMatchNotification(int $userId, int $matchedWithId, int $matchId): void
    {
        try {
            $matchedUser = User::find($matchedWithId);
            $name = $matchedUser ? "{$matchedUser->first_name} {$matchedUser->last_name}" : 'Someone';

            DB::table('notifications')->insert([
                'user_id'     => $userId,
                'title'       => "You matched with {$name}!",
                'description' => 'You can now chat and arrange a trade or sale.',
                'type'        => 'match',
                'data'        => json_encode(['match_id' => $matchId, 'matched_user_id' => $matchedWithId]),
                'is_read'     => false,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create match notification: ' . $e->getMessage());
        }
    }
}
