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
        Schema::create('product_stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->unique();
            $table->integer('quantity');
            $table->decimal('unit_price',10,2);
            // Multiplicador para calcular el precio final en tienda (ej. 1.1 para +10%)
            $table->decimal('price_multiplier', 8, 4)->default(1.0000);
            $table->date('last_update')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stores');
    }
};
