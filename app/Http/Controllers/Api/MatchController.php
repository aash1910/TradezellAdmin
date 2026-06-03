<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingSwipe;
use App\Models\TradezellMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatchController extends Controller
{
    /**
     * GET /matches
     * Returns all active matches for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $matchRows = TradezellMatch::with([
            'userOne:id,first_name,last_name,image',
            'userTwo:id,first_name,last_name,image',
        ])
            ->where(function ($q) use ($user) {
                $q->where('user_one_id', $user->id)
                  ->orWhere('user_two_id', $user->id);
            })
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->get();

        $contextListingsByOwner = $this->loadContextListingsForMatches($user->id, $matchRows);

        $matches = $matchRows->map(function ($m) use ($user, $contextListingsByOwner) {
            $otherId = $m->user_one_id === $user->id ? $m->user_two_id : $m->user_one_id;

            return $this->formatMatch($m, $user->id, $contextListingsByOwner->get($otherId));
        });

        return response()->json([
            'status'  => 'success',
            'matches' => $matches,
        ]);
    }

    /**
     * GET /matches/{id}
     */
    public function show(Request $request, $id)
    {
        $user  = $request->user();
        $match = TradezellMatch::with([
            'userOne:id,first_name,last_name,image',
            'userTwo:id,first_name,last_name,image',
        ])->findOrFail($id);

        // Ensure the user belongs to this match
        if ($match->user_one_id !== $user->id && $match->user_two_id !== $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Not found'], 404);
        }

        $otherId = $match->user_one_id === $user->id ? $match->user_two_id : $match->user_one_id;
        $contextListings = $this->loadContextListingsForMatches($user->id, collect([$match]));

        return response()->json([
            'status' => 'success',
            'match'  => $this->formatMatch($match, $user->id, $contextListings->get($otherId)),
        ]);
    }

    /**
     * DELETE /matches/{id}  — unmatch
     */
    public function unmatch(Request $request, $id)
    {
        $user  = $request->user();
        $match = TradezellMatch::findOrFail($id);

        if ($match->user_one_id !== $user->id && $match->user_two_id !== $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Not found'], 404);
        }

        $match->update([
            'status'       => 'unmatched',
            'unmatched_at' => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Unmatched successfully',
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Most recent listing the current user liked from each matched owner.
     *
     * @param  \Illuminate\Support\Collection<int, TradezellMatch>  $matches
     * @return \Illuminate\Support\Collection<int, array>
     */
    private function loadContextListingsForMatches(int $currentUserId, $matches)
    {
        $otherUserIds = $matches
            ->map(fn (TradezellMatch $m) => $m->user_one_id === $currentUserId ? $m->user_two_id : $m->user_one_id)
            ->unique()
            ->values();

        if ($otherUserIds->isEmpty()) {
            return collect();
        }

        $yesSwipes = ListingSwipe::where('user_id', $currentUserId)
            ->whereIn('owner_id', $otherUserIds)
            ->where('direction', 'yes')
            ->orderByDesc('created_at')
            ->get()
            ->unique('owner_id');

        $context = $this->listingsFromSwipes($yesSwipes);

        $missingOwnerIds = $otherUserIds->diff($context->keys())->values();
        if ($missingOwnerIds->isNotEmpty()) {
            $fallbackSwipes = ListingSwipe::where('user_id', $currentUserId)
                ->whereIn('owner_id', $missingOwnerIds)
                ->orderByDesc('created_at')
                ->get()
                ->unique('owner_id');

            $context = $context->merge($this->listingsFromSwipes($fallbackSwipes));
        }

        return $context;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, ListingSwipe>  $swipes
     * @return \Illuminate\Support\Collection<int, array>
     */
    private function listingsFromSwipes($swipes)
    {
        $listingIds = $swipes->pluck('listing_id')->filter()->unique()->values();
        if ($listingIds->isEmpty()) {
            return collect();
        }

        $listings = Listing::with('user:id,first_name,last_name,image')
            ->whereIn('id', $listingIds)
            ->get()
            ->keyBy('id');

        return $swipes->mapWithKeys(function (ListingSwipe $swipe) use ($listings) {
            $listing = $listings->get($swipe->listing_id);

            return $listing
                ? [$swipe->owner_id => $this->listingSnapshot($listing)]
                : [];
        });
    }

    private function listingSnapshot(Listing $listing): array
    {
        $owner = $listing->user;

        return [
            'id'        => $listing->id,
            'title'     => $listing->title,
            'type'      => $listing->type,
            'condition' => $listing->condition,
            'price'     => $listing->price,
            'currency'  => $listing->currency,
            'status'    => $listing->status,
            'images'    => $listing->getPublicImageUrls(),
            'user'      => $owner ? [
                'id'         => $owner->id,
                'first_name' => $owner->first_name,
                'last_name'  => $owner->last_name,
                'image'      => $owner->image,
            ] : null,
        ];
    }

    private function formatMatch(TradezellMatch $match, int $currentUserId, ?array $contextListing = null): array
    {
        $other = ($match->user_one_id === $currentUserId) ? $match->userTwo : $match->userOne;

        return [
            'id'              => $match->id,
            'status'          => $match->status,
            'conversation_id' => $match->conversation_id,
            'created_at'      => $match->created_at,
            'unmatched_at'    => $match->unmatched_at,
            'other_user'      => $other ? [
                'id'         => $other->id,
                'first_name' => $other->first_name,
                'last_name'  => $other->last_name,
                'image'      => $other->image,
            ] : null,
            'context_listing' => $contextListing,
        ];
    }
}
