<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationRequest extends Model
{
    use HasFactory;

    protected $table =
        'application_requests';

    protected $fillable = [
        'request_number',
        'full_name',
        'identifier_value',
        'email',
        'application_name',
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
            ApplicationRequestFile::class,
            'request_id'
        );
    }
}