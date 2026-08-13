<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestFile extends Model
{
    use HasFactory;

    protected $table = 'request_files';

    public $timestamps = false;

    protected $fillable = [
        'request_id',
        'file_type',
        'original_name',
        'stored_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
    ];

    public function dataRequest(): BelongsTo
    {
        return $this->belongsTo(
            DataRequest::class,
            'request_id'
        );
    }
}