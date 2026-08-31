<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =====================================================
        // PERMINTAAN DATA
        // =====================================================

        Schema::table('requests', function (Blueprint $table) {
            $table
                ->string('email', 150)
                ->nullable()
                ->after('identifier_value');
        });


        // =====================================================
        // APLIKASI
        // =====================================================

        Schema::table('application_requests', function (Blueprint $table) {
            $table
                ->string('email', 150)
                ->nullable()
                ->after('identifier_value');
        });


        // =====================================================
        // WEBSITE
        // =====================================================

        Schema::table('website_requests', function (Blueprint $table) {
            $table
                ->string('email', 150)
                ->nullable()
                ->after('identifier_value');
        });


        // =====================================================
        // WIFI / INTERNET
        // =====================================================

        Schema::table('wifi_internet_requests', function (Blueprint $table) {
            $table
                ->string('email', 150)
                ->nullable()
                ->after('identifier_value');
        });
    }


    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn('email');
        });

        Schema::table('application_requests', function (Blueprint $table) {
            $table->dropColumn('email');
        });

        Schema::table('website_requests', function (Blueprint $table) {
            $table->dropColumn('email');
        });

        Schema::table('wifi_internet_requests', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};