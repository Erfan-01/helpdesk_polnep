<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacilityRequestFile extends Model
{
    use HasFactory;

    protected $table =
        'facility_request_files';

    protected $fillable = [
        'facility_request_id',
        'original_name',
        'stored_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(
            FacilityRequest::class,
            'facility_request_id'
        );
    }
}