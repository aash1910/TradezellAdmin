<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        $matches = TradezellMatch::with([
            'userOne:id,first_name,last_name,image',
            'userTwo:id,first_name,last_name,image',
        ])
            ->where(function ($q) use ($user) {
                $q->where('user_one_id', $user->id)
                  ->orWhere('user_two_id', $user->id);
            })
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($m) => $this->formatMatch($m, $user->id));

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

        return response()->json([
            'status' => 'success',
            'match'  => $this->formatMatch($match, $user->id),
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

    private function formatMatch(TradezellMatch $match, int $currentUserId): array
    {
        $other = ($match->user_one_id === $currentUserId) ? $match->userTwo : $match->userOne;

        return [
            'id'             => $match->id,
            'status'         => $match->status,
            'conversation_id'=> $match->conversation_id,
            'created_at'     => $match->created_at,
            'unmatched_at'   => $match->unmatched_at,
            'other_user'     => $other ? [
                'id'         => $other->id,
                'first_name' => $other->first_name,
                'last_name'  => $other->last_name,
                'image'      => $other->image,
            ] : null,
        ];
    }
}
