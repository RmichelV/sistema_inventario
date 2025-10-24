<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('purchases') && !Schema::hasColumn('purchases', 'unit_price')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->decimal('unit_price', 10, 2)->default(0)->after('purchase_quantity');
                $table->decimal('total_price', 12, 2)->default(0)->after('unit_price');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                if (Schema::hasColumn('purchases', 'total_price')) {
                    $table->dropColumn('total_price');
                }
                if (Schema::hasColumn('purchases', 'unit_price')) {
                    $table->dropColumn('unit_price');
                }
            });
        }
    }
};
