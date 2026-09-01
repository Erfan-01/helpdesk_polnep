<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'facility_requests',
            function (Blueprint $table) {
                $table->id();

                // FAS-2026-000001
                $table
                    ->string('request_number', 30)
                    ->nullable()
                    ->unique();

                // Data pelapor
                $table->string(
                    'full_name',
                    150
                );

                $table->string(
                    'identifier_value',
                    30
                );

                $table->string(
                    'email',
                    150
                );

                // Detail lokasi
                $table->string(
                    'building_name',
                    150
                );

                $table->string(
                    'floor',
                    50
                );

                $table->string(
                    'room_name',
                    150
                );

                // Fasilitas
                $table->string(
                    'facility_type',
                    100
                );

                $table->text(
                    'description'
                );

                // Status
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

                // Jawaban admin
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
                    'resolved_at'
                )->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'facility_requests'
        );
    }
};