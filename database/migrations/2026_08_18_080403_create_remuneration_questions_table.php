<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'remuneration_questions',
            function (Blueprint $table) {

                $table->id();

                $table
                    ->string(
                        'question_number',
                        30
                    )
                    ->nullable()
                    ->unique();

                $table->enum(
                    'user_category',
                    [
                        'dosen',
                        'tenaga_kependidikan',
                        'unit_kerja',
                    ]
                );

                /*
                 * units.id pada database menggunakan
                 * INT UNSIGNED, sehingga unit_id juga
                 * dibuat unsignedInteger.
                 */
                $table->unsignedInteger(
                    'unit_id'
                );

                $table->string(
                    'full_name',
                    150
                );

                $table->string(
                    'nip',
                    30
                );

                $table->string(
                    'email',
                    150
                );

                $table->string(
                    'phone',
                    20
                );

                $table->string(
                    'question_title',
                    200
                );

                $table->text(
                    'question_content'
                );

                $table->enum(
                    'status',
                    [
                        'menunggu_verifikasi',
                        'diproses',
                        'dijawab',
                        'selesai',
                        'ditolak',
                    ]
                )->default(
                    'menunggu_verifikasi'
                );

                $table->text(
                    'answer'
                )->nullable();

                $table->string(
                    'estimated_response',
                    50
                )->default(
                    '1-2 Hari Kerja'
                );

                $table->dateTime(
                    'answered_at'
                )->nullable();

                $table->timestamps();

                $table
                    ->foreign('unit_id')
                    ->references('id')
                    ->on('units')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'remuneration_questions'
        );
    }
};