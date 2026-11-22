<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel users untuk role Admin dan Penyewa.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 16)->unique();
            $table->string('nama');
            $table->string('no_telp', 20);
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role', 20)->default('penyewa'); // admin | penyewa
            $table->string('status', 20)->default('pending'); // pending | approved | rejected
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel users.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};