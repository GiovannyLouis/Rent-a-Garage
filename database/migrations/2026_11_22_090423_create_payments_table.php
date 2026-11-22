<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel payments untuk riwayat pembayaran sewa awal & perpanjangan.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained('rentals')->cascadeOnDelete();
            $table->string('tujuan', 30); // sewa_awal | perpanjangan
            $table->string('tipe_sewa', 20); // harian | bulanan
            $table->unsignedBigInteger('nominal');
            $table->string('bukti_bayar');
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel payments.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};