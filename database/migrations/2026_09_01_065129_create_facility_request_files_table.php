<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'facility_request_files',
            function (Blueprint $table) {
                $table->id();

                $table
                    ->foreignId(
                        'facility_request_id'
                    )
                    ->constrained(
                        'facility_requests'
                    )
                    ->cascadeOnDelete();

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

                $table
                    ->unsignedBigInteger(
                        'file_size'
                    )
                    ->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'facility_request_files'
        );
    }
};