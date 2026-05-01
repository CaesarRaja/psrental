<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Menambah metode pembayaran
            $table->string('payment_method')->default('Cash'); // Cash, QRIS, Transfer
            
            // Menambah bukti bayar (upload gambar)
            $table->string('proof_image')->nullable(); 
            
            // Menambah tipe pembayaran
            $table->string('payable_type')->nullable(); // 'reservation' atau 'food'
            $table->integer('payable_id')->nullable(); // ID reservasi atau order makanan
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'proof_image', 'payable_type', 'payable_id']);
        });
    }
};