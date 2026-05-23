<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('barcode')->nullable();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('unit')->default('pcs');
            $table->decimal('buy_price', 15, 2)->default(0);
            $table->decimal('sell_price', 15, 2)->default(0);
            $table->integer('stock_qty')->default(0);
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            // barcode unique per branch, NULLs exempt
            $table->unique(['barcode', 'branch_id'], 'products_barcode_branch_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
