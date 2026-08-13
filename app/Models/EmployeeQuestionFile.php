<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeQuestionFile extends Model
{
    use HasFactory;

    protected $table = 'employee_question_files';

    public $timestamps = false;

    protected $fillable = [
        'question_id',
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

    public function employeeQuestion(): BelongsTo
    {
        return $this->belongsTo(
            EmployeeQuestion::class,
            'question_id'
        );
    }
}