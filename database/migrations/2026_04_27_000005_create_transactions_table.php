<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('trx_no')->unique();
            $table->date('trx_date');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->enum('payment_method', ['Cash', 'Tempo'])->default('Cash');
            $table->enum('payment_status', ['Lunas', 'Belum Lunas'])->default('Lunas');
            $table->timestamp('paid_at')->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
