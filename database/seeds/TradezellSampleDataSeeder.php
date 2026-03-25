<?php

namespace Database\Seeders;

use App\User;
use App\Models\Faq;
use App\Models\Listing;
use App\Models\ListingSwipe;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Page;
use App\Models\Payment;
use App\Models\Report;
use App\Models\TradezellMatch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TradezellSampleDataSeeder extends Seeder
{
    public function run()
    {
        $textPrefix = '[Tradezell Sample]';
        $stripeIntentPrefix = 'pi_tradezell_sample_';

        $sampleEmails = array_merge(['ashraful@tradezell.com'], [
            'sample1@tradezell.com',
            'sample2@tradezell.com',
            'sample3@tradezell.com',
            'sample4@tradezell.com',
            'sample5@tradezell.com',
            'sample6@tradezell.com',
            'sample7@tradezell.com',
            'sample8@tradezell.com',
        ]);

        $sampleUsers = User::whereIn('email', $sampleEmails)->get();
        if ($sampleUsers->count() < 2) {
            // UsersTableSeeder should have created demo users; bail out to avoid FK failures.
            return;
        }

        $sampleUserIds = $sampleUsers->pluck('id')->values()->all();

        // -----------------------------
        // Clean up existing sample data
        // -----------------------------

        // Listings + dependent swipes/reports
        $existingListingIds = Listing::where('title', 'like', "{$textPrefix}%")->pluck('id')->values()->all();
        if (count($existingListingIds)) {
            ListingSwipe::whereIn('listing_id', $existingListingIds)->delete();
            Report::where('reportable_type', 'listing')->whereIn('reportable_id', $existingListingIds)->delete();
            Listing::whereIn('id', $existingListingIds)->delete();
        }

        // Matches between demo users
        TradezellMatch::whereIn('user_one_id', $sampleUserIds)
            ->whereIn('user_two_id', $sampleUserIds)
            ->delete();

        // Messages + notifications by text prefix
        Message::where('message', 'like', "{$textPrefix}%")->delete();
        Notification::where('title', 'like', "{$textPrefix}%")->delete();

        // Payments by Stripe intent prefix (keeps it independent from legacy `packages` / `orders`)
        Payment::where('stripe_payment_intent_id', 'like', "{$stripeIntentPrefix}%")->delete();

        // Pages + FAQ by title prefix
        Page::where('title', 'like', "{$textPrefix}%")->delete();
        Faq::where('title', 'like', "{$textPrefix}%")->delete();

        // -----------------------------
        // Seed Pages + FAQs
        // -----------------------------

        for ($i = 1; $i <= 10; $i++) {
            $slug = Str::slug("{$textPrefix} Page {$i}");

            Page::create([
                'title' => "{$textPrefix} Page {$i}",
                'url_title' => $slug,
                'header_image' => null, // keep null to avoid file uploads in seeding
                'content' => 'Sample content for demo/testing purposes.',
            ]);
        }

        for ($i = 1; $i <= 10; $i++) {
            Faq::create([
                'title' => "{$textPrefix} FAQ {$i}",
                'description' => 'Sample FAQ answer for Tradezell demo.',
                'is_active' => true,
            ]);
        }

        // -----------------------------
        // Seed Listings
        // -----------------------------

        $types = ['trade', 'sell', 'both'];
        $conditions = ['new', 'like_new', 'good', 'fair', 'poor'];
        $categories = ['Electronics', 'Fashion', 'Home', 'Books', 'Shoes', 'Sports', 'Accessories'];
        $statuses = ['active', 'paused', 'sold', 'traded'];

        $latBase = 6.5244;   // approx. Lagos (placeholder demo center)
        $lngBase = 3.3792;

        $listings = [];
        for ($i = 1; $i <= 10; $i++) {
            $ownerId = $sampleUserIds[($i - 1) % count($sampleUserIds)];
            $type = $types[($i - 1) % count($types)];

            $price = ($type === 'sell' || $type === 'both') ? (string) (10 + $i * 5) : null;

            $listings[] = Listing::create([
                'user_id' => $ownerId,
                'type' => $type,
                'title' => "{$textPrefix} Listing #{$i}",
                'description' => "Sample listing description for item #{$i}.",
                'condition' => $conditions[($i - 1) % count($conditions)],
                'category' => $categories[($i - 1) % count($categories)],
                'price' => $price,
                'currency' => 'USD',
                'images' => [
                    "/uploads/sample/listing_{$i}_1.jpg",
                    "/uploads/sample/listing_{$i}_2.jpg",
                ],
                'status' => $statuses[($i - 1) % count($statuses)],
                'lat' => $latBase + ($i * 0.001),
                'lng' => $lngBase + ($i * 0.001),
            ]);
        }

        // -----------------------------
        // Seed Listing Swipes
        // -----------------------------

        $swipesTarget = 10;
        $swipesInserted = 0;
        $seenSwipeKeys = [];
        $attempts = 0;

        while ($swipesInserted < $swipesTarget && $attempts < 200) {
            $attempts++;
            $idx = $attempts % count($listings);
            $listing = $listings[$idx];

            $ownerId = $listing->user_id;
            $candidateId = $sampleUserIds[($attempts + 1) % count($sampleUserIds)];
            if ($candidateId == $ownerId) {
                $candidateId = $sampleUserIds[($attempts + 2) % count($sampleUserIds)];
            }

            $direction = ($attempts % 2 === 0) ? 'yes' : 'no';

            $key = "{$candidateId}-{$listing->id}";
            if (isset($seenSwipeKeys[$key])) {
                continue;
            }
            $seenSwipeKeys[$key] = true;

            ListingSwipe::create([
                'user_id' => $candidateId,
                'listing_id' => $listing->id,
                'owner_id' => $ownerId,
                'direction' => $direction,
            ]);

            $swipesInserted++;
        }

        // -----------------------------
        // Seed Matches
        // -----------------------------

        $matchesTarget = 10;
        $matchesInserted = 0;
        $seenMatchKeys = [];
        $attempts = 0;

        while ($matchesInserted < $matchesTarget && $attempts < 200) {
            $attempts++;
            $u1 = $sampleUserIds[$attempts % count($sampleUserIds)];
            $u2 = $sampleUserIds[($attempts + 1) % count($sampleUserIds)];
            if ($u1 === $u2) {
                $u2 = $sampleUserIds[($attempts + 2) % count($sampleUserIds)];
            }

            $key = "{$u1}-{$u2}";
            if (isset($seenMatchKeys[$key])) {
                continue;
            }
            $seenMatchKeys[$key] = true;

            $status = ($attempts % 3 === 0) ? 'unmatched' : 'active';

            TradezellMatch::create([
                'user_one_id' => $u1,
                'user_two_id' => $u2,
                'status' => $status,
                'unmatched_at' => $status === 'unmatched' ? now()->subDays($attempts) : null,
                'conversation_id' => null,
            ]);

            $matchesInserted++;
        }

        // -----------------------------
        // Seed Reports
        // -----------------------------

        $reasons = ['Spam', 'Inappropriate content', 'Fake listing', 'Scam behavior', 'Duplicate listing'];
        for ($i = 1; $i <= 10; $i++) {
            $reporterId = $sampleUserIds[($i + 2) % count($sampleUserIds)];
            $listing = $listings[($i - 1) % count($listings)];

            Report::create([
                'reporter_id' => $reporterId,
                'reportable_type' => 'listing',
                'reportable_id' => $listing->id,
                'reason' => "{$textPrefix} {$reasons[($i - 1) % count($reasons)]}",
                'description' => "Sample report description for listing #{$listing->id}.",
                'status' => 'pending',
            ]);
        }

        // -----------------------------
        // Seed Messages
        // -----------------------------

        for ($i = 1; $i <= 10; $i++) {
            $senderId = $sampleUserIds[($i - 1) % count($sampleUserIds)];
            $receiverId = $sampleUserIds[($i + 3) % count($sampleUserIds)];
            if ($senderId === $receiverId) {
                $receiverId = $sampleUserIds[($i + 4) % count($sampleUserIds)];
            }

            Message::create([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'message' => "{$textPrefix} Message #{$i}",
                'is_read' => ($i % 2 === 0),
            ]);
        }

        // -----------------------------
        // Seed Notifications
        // -----------------------------

        for ($i = 1; $i <= 10; $i++) {
            $userId = $sampleUserIds[($i - 1) % count($sampleUserIds)];

            Notification::create([
                'user_id' => $userId,
                'title' => "{$textPrefix} Notification #{$i}",
                'description' => 'Sample notification for demo/testing purposes.',
                'is_read' => ($i % 3 === 0),
                'type' => 'trade',
                'data' => [
                    'listing_id' => $listings[($i - 1) % count($listings)]->id,
                ],
            ]);
        }

        // -----------------------------
        // Seed Payments (listing MVP alias)
        // -----------------------------

        $paymentStatuses = ['pending', 'processing', 'succeeded', 'failed', 'canceled'];
        $paymentTypes = ['escrow', 'release', 'refund', 'withdrawal'];

        for ($i = 1; $i <= 10; $i++) {
            $userId = $sampleUserIds[($i - 1) % count($sampleUserIds)];

            $status = $paymentStatuses[($i - 1) % count($paymentStatuses)];
            $paymentType = $paymentTypes[($i - 1) % count($paymentTypes)];

            $amount = 20.00 + ($i * 7.5);

            $processedAt = $status === 'succeeded' ? now()->subDays($i) : null;
            $stripeFee = $status === 'succeeded' ? round($amount * 0.05, 2) : null;
            $availableOn = $status === 'succeeded' ? now()->addDays(2) : null;

            Payment::create([
                'package_id' => null,
                'user_id' => $userId,
                'payment_gateway' => 'stripe',
                'stripe_payment_intent_id' => "{$stripeIntentPrefix}{$i}",
                'stripe_payment_method_id' => null,
                'amount' => $amount,
                'currency' => 'USD',
                'status' => $status,
                'payment_type' => $paymentType,
                'refund_reason' => null,
                'processed_at' => $processedAt,
                'available_on' => $availableOn,
                'stripe_fee' => $stripeFee,
            ]);
        }
    }
}

