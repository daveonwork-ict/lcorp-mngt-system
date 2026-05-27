<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table): void {
            $table->string('branch_code')->nullable()->after('id')->unique();
            $table->string('branch_name')->nullable()->after('branch_code');
            $table->string('contact_number')->nullable()->after('address');
            $table->string('email')->nullable()->after('contact_number');
            $table->foreignId('manager_id')->nullable()->after('email')->constrained('users')->nullOnDelete();
            $table->time('opening_time')->nullable()->after('manager_id');
            $table->time('closing_time')->nullable()->after('opening_time');
            $table->string('operational_status')->default('active')->after('closing_time');
            $table->string('status')->default('active')->after('operational_status');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('manager_id');
            $table->dropColumn([
                'branch_code',
                'branch_name',
                'contact_number',
                'email',
                'opening_time',
                'closing_time',
                'operational_status',
                'status',
            ]);
        });
    }
};
