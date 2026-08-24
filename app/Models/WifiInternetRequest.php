<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WifiInternetRequest extends Model
{
    use HasFactory;

    protected $table =
        'wifi_internet_requests';

    protected $fillable = [
        'request_number',
        'full_name',
        'identifier_value',
        'building_name',
        'room_name',
        'description',
        'status',
        'answer',
        'estimated_response',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];
}