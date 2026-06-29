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
            // global_search defaults on for new users (see User::defaultSettingsForNewUser)
            $settings = $this->getUserSettings($user);
            $globalSearch = $settings['global_search'] ?? true;

            if (!$globalSearch) {
                $query->whereRaw(
                    '(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) <= ?',
                    [$lat, $lng, $lat, $radiusKm]
                );
            }
        }

        $listings = $query->orderByDesc('created_at')->paginate(20);
        $listings->setCollection(
            $listings->getCollection()->map(fn (Listing $listing) => $this->listingForApi($listing))
        );

        return response()->json([
            'status'   => 'success',
            'listings' => $listings,
        ]);
    }

    // ── My listings ───────────────────────────────────────────────────────────

    public function myListings(Request $request)
    {
        $listings = Listing::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'status'   => 'success',
            'listings' => $listings->map(fn (Listing $listing) => $this->listingForApi($listing)),
        ]);
    }

    /**
     * GET /listings/liked
     * Yes-swipes on others' listings where there is no active match with the owner yet.
     */
    public function liked(Request $request)
    {
        $user = $request->user();
        $matchedOwnerIds = $this->getMatchedUserIds($user->id);

        $swipes = ListingSwipe::where('user_id', $user->id)
            ->where('direction', 'yes')
            ->when(count($matchedOwnerIds) > 0, fn ($q) => $q->whereNotIn('owner_id', $matchedOwnerIds))
            ->orderByDesc('created_at')
            ->get();

        $listingIds = $swipes->pluck('listing_id')->unique()->values()->all();
        $listingsById = Listing::with('user:id,first_name,last_name,image')
            ->whereIn('id', $listingIds)
            ->whereIn('status', ['active', 'paused'])
            ->get()
            ->keyBy('id');

        $liked = $swipes
            ->filter(fn (ListingSwipe $swipe) => $listingsById->has($swipe->listing_id))
            ->map(function (ListingSwipe $swipe) use ($listingsById) {
                $listing = $listingsById->get($swipe->listing_id);

                return [
                    'liked_at' => $swipe->created_at,
                    'listing'  => $this->listingForApi($listing),
                ];
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'liked'  => $liked,
        ]);
    }

    // ── Single listing ────────────────────────────────────────────────────────

    public function show(Request $request, $id)
    {
        $listing = Listing::with('user:id,first_name,last_name,image')->findOrFail($id);

        return response()->json([
            'status'  => 'success',
            'listing' => $this->listingForApi($listing),
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
            'images'      => Listing::processImagesForStorage($request->images ?? []),
            'lat'         => $request->lat,
            'lng'         => $request->lng,
            'status'      => 'active',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Listing created successfully',
            'listing' => $this->listingForApi($listing),
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

        $data = $request->only([
            'type', 'title', 'description', 'condition', 'category',
            'price', 'currency', 'lat', 'lng',
        ]);

        if ($request->has('images')) {
            $existingImages = $listing->images ?? [];
            $newImages = Listing::processImagesForStorage($request->images ?? []);
            $removedImages = array_diff($existingImages, $newImages);

            Listing::deleteStoredImages($removedImages);
            $data['images'] = $newImages;
        }

        $listing->update($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Listing updated successfully',
            'listing' => $this->listingForApi($listing->fresh()),
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
            'listing' => $this->listingForApi($listing),
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

        $priorSwipe = ListingSwipe::where('user_id', $swiper->id)
            ->where('listing_id', $listing->id)
            ->first();

        // Record the swipe (upsert so retries are idempotent)
        ListingSwipe::updateOrCreate(
            ['user_id' => $swiper->id, 'listing_id' => $listing->id],
            ['direction' => $request->direction, 'owner_id' => $owner->id]
        );

        $isNewYes = $request->direction === 'yes'
            && (!$priorSwipe || $priorSwipe->direction !== 'yes');

        if ($request->direction === 'no') {
            $unmatched = false;
            $activeMatch = TradezellMatch::where('status', 'active')
                ->where(function ($q) use ($swiper, $owner) {
                    $q->where(function ($inner) use ($swiper, $owner) {
                        $inner->where('user_one_id', $swiper->id)->where('user_two_id', $owner->id);
                    })->orWhere(function ($inner) use ($swiper, $owner) {
                        $inner->where('user_one_id', $owner->id)->where('user_two_id', $swiper->id);
                    });
                })
                ->first();

            if ($activeMatch) {
                $activeMatch->update([
                    'status'       => 'unmatched',
                    'unmatched_at' => now(),
                ]);
                $unmatched = true;
            }

            $removedLike = $priorSwipe && $priorSwipe->direction === 'yes';

            return response()->json([
                'status'       => 'success',
                'matched'      => false,
                'unmatched'    => $unmatched,
                'removed_like' => $removedLike,
            ]);
        }

        // Check for mutual match: has the owner already swiped yes on ANY of swiper's listings?
        $ownerLikedSwiper = ListingSwipe::where('user_id', $owner->id)
            ->where('owner_id', $swiper->id)
            ->where('direction', 'yes')
            ->exists();

        if (!$ownerLikedSwiper) {
            if ($isNewYes) {
                $this->createLikeNotification($owner->id, $swiper, $listing);
            }

            return response()->json(['status' => 'success', 'matched' => false]);
        }

        $match = $this->findOrCreateMatch($swiper, $owner);

        return response()->json([
            'status'  => 'success',
            'matched' => true,
            'match'   => $this->formatMatch($match, $swiper->id),
        ]);
    }

    /**
     * DELETE /listings/{id}/like
     * Remove a yes-swipe so the listing can appear in the feed again.
     */
    public function unlike(Request $request, $id)
    {
        $deleted = ListingSwipe::where('user_id', $request->user()->id)
            ->where('listing_id', $id)
            ->where('direction', 'yes')
            ->delete();

        if (!$deleted) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Like not found',
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Like removed',
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function listingForApi(Listing $listing): Listing
    {
        $listing->setAttribute('images', $listing->getPublicImageUrls());

        return $listing;
    }

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

    /** @return int[] */
    private function getMatchedUserIds(int $userId): array
    {
        return TradezellMatch::where('status', 'active')
            ->where(function ($q) use ($userId) {
                $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId);
            })
            ->get()
            ->map(fn (TradezellMatch $m) => $m->user_one_id === $userId ? $m->user_two_id : $m->user_one_id)
            ->unique()
            ->values()
            ->all();
    }

    private function findOrCreateMatch(User $swiper, User $owner): TradezellMatch
    {
        $userOneId = min($swiper->id, $owner->id);
        $userTwoId = max($swiper->id, $owner->id);

        return DB::transaction(function () use ($swiper, $owner, $userOneId, $userTwoId) {
            $existing = TradezellMatch::where('user_one_id', $userOneId)
                ->where('user_two_id', $userTwoId)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if ($existing->status !== 'active') {
                    $existing->update([
                        'status'       => 'active',
                        'unmatched_at' => null,
                    ]);
                    $this->createMatchNotification($swiper->id, $owner->id, $existing->id);
                    $this->createMatchNotification($owner->id, $swiper->id, $existing->id);
                }

                return $existing;
            }

            $newMatch = TradezellMatch::create([
                'user_one_id' => $userOneId,
                'user_two_id' => $userTwoId,
                'status'      => 'active',
            ]);

            $this->createMatchNotification($swiper->id, $owner->id, $newMatch->id);
            $this->createMatchNotification($owner->id, $swiper->id, $newMatch->id);

            return $newMatch;
        });
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

    private function createLikeNotification(int $ownerId, User $swiper, Listing $listing): void
    {
        try {
            $name = trim("{$swiper->first_name} {$swiper->last_name}") ?: 'Someone';

            DB::table('notifications')->insert([
                'user_id'     => $ownerId,
                'title'       => "{$name} liked your listing",
                'description' => "\"{$listing->title}\" — like one of their listings back to match!",
                'type'        => 'like',
                'data'        => json_encode([
                    'listing_id' => $listing->id,
                    'swiper_id'  => $swiper->id,
                ]),
                'is_read'     => false,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create like notification: ' . $e->getMessage());
        }
    }
}
