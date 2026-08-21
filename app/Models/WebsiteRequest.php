<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebsiteRequest extends Model
{
    use HasFactory;

    protected $table = 'website_requests';

    protected $fillable = [
        'request_number',
        'full_name',
        'identifier_value',
        'website_name',
        'issue_type',
        'description',
        'status',
        'answer',
        'estimated_response',
        'answered_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
    ];

    public function files(): HasMany
    {
        return $this->hasMany(
            WebsiteRequestFile::class,
            'request_id'
        );
    }
}