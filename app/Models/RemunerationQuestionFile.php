<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RemunerationQuestionFile extends Model
{
    use HasFactory;

    protected $table =
        'remuneration_question_files';

    public $timestamps = false;

    protected $fillable = [
        'question_id',
        'original_name',
        'stored_name',
        'file_path',
        'mime_type',
        'file_size',
        'created_at',
    ];

    /**
     * Pertanyaan Remunerasi.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(
            RemunerationQuestion::class,
            'question_id'
        );
    }
}