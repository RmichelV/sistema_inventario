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
        Schema::table('product_branches', function (Blueprint $table) {
            $table->unique(['branch_id', 'product_id'], 'product_branches_branch_product_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_branches', function (Blueprint $table) {
            $table->dropUnique('product_branches_branch_product_unique');
        });
    }
};
