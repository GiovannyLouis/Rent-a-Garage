<?php

namespace Tests\Feature;

use App\Models\Garage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_all_report_tabs(): void
    {
        $admin = User::create([
            'nik' => '0000000000000001',
            'nama' => 'Admin',
            'no_telp' => '081111111111',
            'email' => 'admin@test.com',
            'password' => 'admin123',
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
        ]);

        $penyewa = User::create([
            'nik' => '3201010101010099',
            'nama' => 'Budi Santoso',
            'no_telp' => '081234567899',
            'email' => 'budi@test.com',
            'password' => 'password123',
            'role' => User::ROLE_PENYEWA,
            'status' => User::STATUS_APPROVED,
        ]);

        $this->actingAs($admin)->get(route('admin.reports.index', ['tab' => 'month', 'year' => 2026, 'month' => 4]))->assertOk();
        $this->actingAs($admin)->get(route('admin.reports.index', ['tab' => 'day', 'year' => 2026, 'month' => 4, 'day' => 6]))->assertOk();
        $this->actingAs($admin)->get(route('admin.reports.index', ['tab' => 'garage_type', 'tipe_garasi' => Garage::TIPE_MOBIL]))->assertOk();
        $this->actingAs($admin)->get(route('admin.reports.index', ['tab' => 'user', 'user_id' => $penyewa->id]))->assertOk();
    }
}