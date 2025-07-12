<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Order;
use Stripe\Stripe;
use Stripe\Refund;
use Stripe\Exception\ApiErrorException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessEscrowRefunds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'escrow:process-refunds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process automatic refunds for packages without droppers after 24 hours';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting escrow refund processing...');

        // Find packages that are older than 24 hours and don't have orders
        $packagesToRefund = Package::where('created_at', '<=', Carbon::now()->subHours(24))
            ->whereDoesntHave('order')
            ->whereHas('payments', function ($query) {
                $query->where('payment_type', 'escrow')
                      ->where('status', 'succeeded');
            })
            ->whereDoesntHave('payments', function ($query) {
                $query->where('payment_type', 'refund');
            })
            ->get();

        $this->info("Found {$packagesToRefund->count()} packages eligible for refund");

        $successCount = 0;
        $errorCount = 0;

        foreach ($packagesToRefund as $package) {
            try {
                $this->processRefund($package);
                $successCount++;
                $this->info("Processed refund for package ID: {$package->id}");
            } catch (\Exception $e) {
                $errorCount++;
                $this->error("Failed to process refund for package ID: {$package->id} - {$e->getMessage()}");
                Log::error("Escrow refund error for package {$package->id}: " . $e->getMessage());
            }
        }

        $this->info("Refund processing completed. Success: {$successCount}, Errors: {$errorCount}");

        return 0;
    }

    /**
     * Process refund for a specific package
     */
    private function processRefund(Package $package)
    {
        // Get the escrow payment
        $escrowPayment = $package->payments()
            ->where('payment_type', 'escrow')
            ->where('status', 'succeeded')
            ->first();

        if (!$escrowPayment) {
            throw new \Exception('No escrow payment found');
        }

        // Check if refund already exists
        $existingRefund = $package->payments()
            ->where('payment_type', 'refund')
            ->first();

        if ($existingRefund) {
            throw new \Exception('Refund already exists');
        }

        // Create Stripe refund
        $refund = Refund::create([
            'payment_intent' => $escrowPayment->stripe_payment_intent_id,
            'metadata' => [
                'package_id' => $package->id,
                'user_id' => $package->sender_id,
                'reason' => 'Automatic refund - No dropper assigned within 24 hours',
                'processed_by' => 'system',
            ],
        ]);

        // Create refund payment record
        Payment::create([
            'package_id' => $package->id,
            'user_id' => $package->sender_id,
            'stripe_payment_intent_id' => $refund->id,
            'amount' => -$escrowPayment->amount, // Negative amount for refund
            'currency' => $escrowPayment->currency,
            'status' => $refund->status,
            'payment_type' => 'refund',
            'refund_reason' => 'Automatic refund - No dropper assigned within 24 hours',
            'processed_at' => now(),
        ]);

        // Update package status if needed
        if ($package->status === 'pending') {
            $package->update(['status' => 'expired']);
        }
    }
} 