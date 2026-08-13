<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'units';

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
        return $this->hasMany(DataRequest::class, 'unit_id');
    }

    public function employeeQuestions(): HasMany
    {
        return $this->hasMany(EmployeeQuestion::class, 'unit_id');
    }
}