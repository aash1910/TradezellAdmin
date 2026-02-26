<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Package;
use App\User;

/**
 * Payment Model
 * 
 * Handles payment transactions including escrow, releases, refunds, and withdrawals.
 * Integrates with Stripe payment gateway to process and track payment information.
 * 
 * @author Ashraful Islam
 */
class Payment extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'payments';
    protected $guarded = ['id'];

    protected $fillable = [
        'package_id',
        'user_id',
        'payment_gateway',
        'stripe_payment_intent_id',
        'stripe_payment_method_id',
        'momo_reference_id',
        'momo_phone_number',
        'amount',
        'currency',
        'status',
        'payment_type',
        'refund_reason',
        'processed_at',
        'available_on',
        'stripe_fee',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'stripe_fee' => 'decimal:2',
        'processed_at' => 'datetime',
        'available_on' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public function getPaymentInfoAttribute()
    {
        return '$' . number_format($this->amount, 2) . ' - ' . ucfirst($this->status) . ' - ' . ucfirst($this->payment_type);
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeEscrow($query)
    {
        return $query->where('payment_type', 'escrow');
    }

    public function scopeRefunded($query)
    {
        return $query->where('payment_type', 'refund');
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'succeeded');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'badge-warning',
            'processing' => 'badge-info',
            'succeeded' => 'badge-success',
            'failed' => 'badge-danger',
            'canceled' => 'badge-secondary',
        ];

        return $badges[$this->status] ?? 'badge-secondary';
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
} 