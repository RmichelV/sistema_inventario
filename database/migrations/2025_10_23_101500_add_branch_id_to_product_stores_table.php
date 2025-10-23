<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_stores', function (Blueprint $table) {
            // Intentamos eliminar el índice único sobre product_id si existe (comprobando con SHOW INDEX)
            $exists = count(DB::select("SHOW INDEX FROM `product_stores` WHERE Key_name = 'product_stores_product_id_unique'")) > 0;
            if ($exists) {
                DB::statement('ALTER TABLE `product_stores` DROP INDEX `product_stores_product_id_unique`');
            }

            // Agregar columna branch_id (nullable temporalmente)
            if (!Schema::hasColumn('product_stores', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('product_id');
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            }

            // Crear índice compuesto único (branch_id, product_id)
            $table->unique(['branch_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_stores', function (Blueprint $table) {
            // Eliminar índice compuesto si existe (comprobando con SHOW INDEX)
            $existsComposite = count(DB::select("SHOW INDEX FROM `product_stores` WHERE Key_name = 'product_stores_branch_id_product_id_unique'")) > 0;
            if ($existsComposite) {
                DB::statement('ALTER TABLE `product_stores` DROP INDEX `product_stores_branch_id_product_id_unique`');
            }

            if (Schema::hasColumn('product_stores', 'branch_id')) {
                // Eliminar FK y columna
                try {
                    $table->dropForeign(['branch_id']);
                } catch (\Throwable $e) {
                }
                $table->dropColumn('branch_id');
            }

            // Restaurar índice único sobre product_id si es necesario (comprobar existencia primero)
            $existsPid = count(DB::select("SHOW INDEX FROM `product_stores` WHERE Key_name = 'product_stores_product_id_unique'")) == 0;
            if ($existsPid) {
                try {
                    $table->unique('product_id');
                } catch (\Throwable $e) {
                }
            }
        });
    }
};
