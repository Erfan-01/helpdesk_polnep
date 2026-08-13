<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'request_status_history';

    public $timestamps = false;

    protected $fillable = [
        'request_id',
        'status',
        'note',
    ];

    protected $casts = [
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