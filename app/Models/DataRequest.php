<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataRequest extends Model
{
    use HasFactory;

    protected $table = 'requests';

    protected $fillable = [
        'request_number',
        'requester_type',
        'full_name',
        'identifier_type',
        'identifier_value',
        'unit_id',
        'phone',
        'category_id',
        'information_needed',
        'request_reason',
        'priority',
        'status',
        'estimated_response',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            RequestCategory::class,
            'category_id'
        );
    }

    public function files(): HasMany
    {
        return $this->hasMany(
            RequestFile::class,
            'request_id'
        );
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(
            RequestStatusHistory::class,
            'request_id'
        );
    }
}