<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacilityRequest extends Model
{
    use HasFactory;

    protected $table =
        'facility_requests';

    protected $fillable = [
        'request_number',
        'full_name',
        'identifier_value',
        'email',
        'building_name',
        'floor',
        'room_name',
        'facility_type',
        'description',
        'status',
        'answer',
        'estimated_response',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' =>
            'datetime',
    ];

    public function files(): HasMany
    {
        return $this->hasMany(
            FacilityRequestFile::class,
            'facility_request_id'
        );
    }
}