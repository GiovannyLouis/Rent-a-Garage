<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel garages (Sepeda, Mobil, Mobil Listrik).
     */
    public function up(): void
    {
        Schema::create('garages', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique(); // S001, C001, E001
            $table->string('tipe_garasi', 20); // sepeda | mobil | mobil_listrik
            $table->unsignedInteger('harga_harian');
            $table->unsignedInteger('harga_kwh')->nullable(); // Khusus mobil_listrik
            $table->string('status', 20)->default('available'); // available | used
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel garages.
     */
    public function down(): void
    {
        Schema::dropIfExists('garages');
    }
};