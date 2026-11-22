<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel rentals untuk transaksi penyewaan garasi.
     */
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('garage_id')->constrained('garages');
            $table->string('tipe_kendaraan', 20); // sepeda | sepeda_motor | mobil | mobil_listrik
            $table->string('plat_nomor', 20)->nullable(); // Null jika sepeda
            $table->string('merek_tipe');
            $table->decimal('kapasitas_baterai', 8, 2)->nullable(); // Khusus mobil listrik
            $table->decimal('sisa_baterai', 8, 2)->nullable(); // Khusus mobil listrik
            $table->unsignedInteger('jumlah_hari');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel rentals.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};