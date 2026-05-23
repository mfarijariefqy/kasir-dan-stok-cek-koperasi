<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_barcode_branch_unique');
            // Same barcode is now allowed across different units in the same branch
            $table->unique(['barcode', 'branch_id', 'unit'], 'products_barcode_branch_unit_unique');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_barcode_branch_unit_unique');
            $table->unique(['barcode', 'branch_id'], 'products_barcode_branch_unique');
        });
    }
};
