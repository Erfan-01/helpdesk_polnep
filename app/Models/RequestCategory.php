<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequestCategory extends Model
{
    use HasFactory;

    protected $table = 'request_categories';

    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function dataRequests(): HasMany
    {
        return $this->hasMany(DataRequest::class, 'category_id');
    }
}