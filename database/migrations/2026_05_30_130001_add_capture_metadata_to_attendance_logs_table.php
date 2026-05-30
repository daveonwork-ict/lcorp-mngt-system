<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table): void {
            $table->json('capture_metadata_in')->nullable()->after('device_info_out');
            $table->json('capture_metadata_out')->nullable()->after('capture_metadata_in');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table): void {
            $table->dropColumn(['capture_metadata_in', 'capture_metadata_out']);
        });
    }
};