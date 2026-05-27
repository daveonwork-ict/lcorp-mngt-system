<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->string('status')->default('active')->after('is_system');
        });

        Schema::table('activity_logs', function (Blueprint $table): void {
            $table->string('module_name')->nullable()->after('branch_id');
            $table->string('action_type')->nullable()->after('module_name');
            $table->text('description')->nullable()->after('action_type');
            $table->json('before_value')->nullable()->after('description');
            $table->json('after_value')->nullable()->after('before_value');
            $table->text('user_agent')->nullable()->after('ip_address');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->dropColumn('status');
        });

        Schema::table('activity_logs', function (Blueprint $table): void {
            $table->dropColumn([
                'module_name',
                'action_type',
                'description',
                'before_value',
                'after_value',
                'user_agent',
            ]);
        });
    }
};
