<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Report extends Model
{
    use CrudTrait;
    protected $fillable = [
        'reporter_id',
        'reportable_type',
        'reportable_id',
        'reason',
        'description',
        'status',
    ];

    public function reporter()
    {
        return $this->belongsTo(\App\User::class, 'reporter_id');
    }
}
