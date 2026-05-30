<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('branch_inventories', 'last_stock_in_at')) {
            Schema::table('branch_inventories', function (Blueprint $table): void {
                $table->timestamp('last_stock_in_at')->nullable()->after('reorder_level');
                $table->index('last_stock_in_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('branch_inventories', 'last_stock_in_at')) {
            Schema::table('branch_inventories', function (Blueprint $table): void {
                $table->dropIndex(['last_stock_in_at']);
                $table->dropColumn('last_stock_in_at');
            });
        }
    }
};
