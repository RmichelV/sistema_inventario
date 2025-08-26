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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('code')->unique();
            $table->string('img_product')->nullable();
            $table->integer('quantity_in_stock');
            $table->integer('units_per_box')->nullable();
            $table->integer('minimum_wholesale_quantity')->nullable();
            $table->string('currency_type');
            $table->decimal('unit_price_wholesale');
            $table->decimal('unit_price_retail');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
