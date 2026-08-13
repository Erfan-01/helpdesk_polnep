<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeQuestion extends Model
{
    use HasFactory;

    protected $table = 'employee_questions';

    protected $fillable = [
        'question_number',
        'user_category',
        'unit_id',
        'full_name',
        'nip',
        'email',
        'phone',
        'question_title',
        'question_content',
        'status',
        'answer',
        'estimated_response',
        'answered_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(
            Unit::class,
            'unit_id'
        );
    }

    public function files(): HasMany
    {
        return $this->hasMany(
            EmployeeQuestionFile::class,
            'question_id'
        );
    }
}