<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'remuneration_question_files',
            function (Blueprint $table) {

                $table->id();

                $table->unsignedBigInteger(
                    'question_id'
                );

                $table->string(
                    'original_name',
                    255
                );

                $table->string(
                    'stored_name',
                    255
                );

                $table->string(
                    'file_path',
                    500
                );

                $table->string(
                    'mime_type',
                    100
                )->nullable();

                $table->unsignedBigInteger(
                    'file_size'
                )->nullable();

                $table->timestamp(
                    'created_at'
                )->useCurrent();

                $table
                    ->foreign(
                        'question_id'
                    )
                    ->references('id')
                    ->on(
                        'remuneration_questions'
                    )
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'remuneration_question_files'
        );
    }
};