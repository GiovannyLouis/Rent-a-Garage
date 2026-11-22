<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Mengisi data awal database (akun Admin default).
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'nik' => '0000000000000001',
                'nama' => 'Admin',
                'no_telp' => '081234567890',
                'password' => Hash::make('admin'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_APPROVED,
            ]
        );
    }
}