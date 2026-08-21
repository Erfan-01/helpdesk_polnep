<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteRequestFile extends Model
{
    use HasFactory;

    protected $table =
        'website_request_files';

    public $timestamps = false;

    protected $fillable = [
        'request_id',
        'original_name',
        'stored_name',
        'file_path',
        'mime_type',
        'file_size',
        'created_at',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(
            WebsiteRequest::class,
            'request_id'
        );
    }
}