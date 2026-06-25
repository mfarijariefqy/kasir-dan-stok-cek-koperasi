<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('product_id')->constrained('branches')->nullOnDelete();
        });

        // Backfill existing rows from each item's product — the best available
        // source, since the item's own branch was never recorded before this.
        DB::statement('
            UPDATE transaction_items ti
            INNER JOIN products p ON p.id = ti.product_id
            SET ti.branch_id = p.branch_id
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
