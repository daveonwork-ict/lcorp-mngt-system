<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('employee_code')->nullable()->after('id')->unique();
            $table->string('first_name')->nullable()->after('employee_code');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->string('suffix')->nullable()->after('last_name');
            $table->string('full_name')->nullable()->after('suffix');
            $table->string('username')->nullable()->after('name')->unique();
            $table->string('mobile_number')->nullable()->after('email');
            $table->string('profile_photo')->nullable()->after('mobile_number');
            $table->foreignId('primary_branch_id')->nullable()->after('role_id')->constrained('branches')->nullOnDelete();
            $table->string('status')->default('active')->after('primary_branch_id');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('primary_branch_id');
            $table->dropColumn([
                'employee_code',
                'first_name',
                'middle_name',
                'last_name',
                'suffix',
                'full_name',
                'username',
                'mobile_number',
                'profile_photo',
                'status',
                'last_login_ip',
            ]);
        });
    }
};
