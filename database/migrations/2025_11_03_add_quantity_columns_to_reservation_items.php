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
        Schema::table('reservation_items', function (Blueprint $table) {
            $table->decimal('quantity_from_warehouse', 8, 2)->nullable()->after('quantity_products');
            $table->decimal('quantity_from_store', 8, 2)->nullable()->after('quantity_from_warehouse');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservation_items', function (Blueprint $table) {
            $table->dropColumn(['quantity_from_warehouse', 'quantity_from_store']);
        });
    }
};
