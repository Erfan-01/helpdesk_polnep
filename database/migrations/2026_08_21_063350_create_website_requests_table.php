<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_requests', function (Blueprint $table) {
            $table->id();

            $table->string('request_number', 30)
                ->nullable()
                ->unique();

            $table->string('full_name', 150);

            $table->string('identifier_value', 30);

            $table->string('website_name', 200);

            $table->string('issue_type', 50);

            $table->text('description');

            $table->enum('status', [
                'menunggu_verifikasi',
                'diproses',
                'dijawab',
                'selesai',
                'ditolak',
            ])->default('menunggu_verifikasi');

            $table->text('answer')->nullable();

            $table->string('estimated_response', 50)
                ->default('1-2 Hari Kerja');

            $table->dateTime('answered_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_requests');
    }
};