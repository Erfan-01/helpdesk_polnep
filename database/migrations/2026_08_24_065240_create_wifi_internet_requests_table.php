<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'wifi_internet_requests',
            function (Blueprint $table) {

                $table->id();

                // Nomor tiket:
                // NET-2026-000001
                $table
                    ->string(
                        'request_number',
                        30
                    )
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

                // Lokasi
                $table->string(
                    'building_name',
                    150
                );

                $table->string(
                    'room_name',
                    150
                );

                // Keluhan
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

                // Jawaban / tindak lanjut admin
                $table->text(
                    'answer'
                )->nullable();

                // Estimasi
                $table
                    ->string(
                        'estimated_response',
                        50
                    )
                    ->default(
                        '1-2 Hari Kerja'
                    );

                // Waktu selesai
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
            'wifi_internet_requests'
        );
    }
};