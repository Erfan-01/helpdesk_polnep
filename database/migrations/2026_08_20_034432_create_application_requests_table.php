<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'application_requests',
            function (Blueprint $table) {

                $table->id();

                $table
                    ->string('request_number', 30)
                    ->nullable()
                    ->unique();

                $table->string(
                    'full_name',
                    150
                );

                $table->string(
                    'identifier_value',
                    30
                );

                $table->string(
                    'application_name',
                    150
                );

                $table->enum(
                    'issue_type',
                    [
                        'tidak_bisa_login',
                        'data_tidak_sesuai',
                        'error_sistem',
                        'permintaan_akses',
                    ]
                );

                $table->text(
                    'description'
                );

                $table->enum(
                    'status',
                    [
                        'menunggu_verifikasi',
                        'diproses',
                        'selesai',
                        'ditolak',
                    ]
                )->default(
                    'menunggu_verifikasi'
                );

                $table->text(
                    'answer'
                )->nullable();

                $table
                    ->string(
                        'estimated_response',
                        50
                    )
                    ->default(
                        '1-2 Hari Kerja'
                    );

                $table->dateTime(
                    'answered_at'
                )->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'application_requests'
        );
    }
};