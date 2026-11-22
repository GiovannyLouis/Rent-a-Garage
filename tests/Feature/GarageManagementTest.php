<?php

namespace Tests\Feature;

use App\Models\Garage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GarageManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::create([
            'nik' => '0000000000000001',
            'nama' => 'Admin',
            'no_telp' => '081111111111',
            'email' => 'admin@test.com',
            'password' => 'admin123',
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
        ]);
    }

    public function test_admin_can_create_batch_garages_and_electric_requires_kwh(): void
    {
        $admin = $this->createAdmin();

        // Sepeda: harga_kwh harus null
        $this->actingAs($admin)->post(route('admin.garages.store'), [
            'tipe_garasi' => Garage::TIPE_SEPEDA,
            'jumlah' => 3,
            'harga_harian' => 10000,
        ])->assertRedirect(route('admin.garages.index'));

        $this->assertDatabaseHas('garages', ['kode' => 'S001', 'harga_kwh' => null]);
        $this->assertDatabaseHas('garages', ['kode' => 'S003', 'status' => Garage::STATUS_AVAILABLE]);

        // Mobil listrik tanpa harga_kwh harus gagal
        $this->actingAs($admin)->post(route('admin.garages.store'), [
            'tipe_garasi' => Garage::TIPE_MOBIL_LISTRIK,
            'jumlah' => 2,
            'harga_harian' => 60000,
        ])->assertSessionHasErrors('harga_kwh');

        // Mobil listrik dengan harga_kwh berhasil
        $this->actingAs($admin)->post(route('admin.garages.store'), [
            'tipe_garasi' => Garage::TIPE_MOBIL_LISTRIK,
            'jumlah' => 2,
            'harga_harian' => 60000,
            'harga_kwh' => 2500,
        ])->assertRedirect(route('admin.garages.index'));

        $this->assertDatabaseHas('garages', ['kode' => 'E001', 'harga_kwh' => 2500]);
        $this->assertDatabaseHas('garages', ['kode' => 'E002', 'harga_kwh' => 2500]);
    }

    public function test_admin_cannot_exceed_999_cumulative_limit_per_garage_type(): void
    {
        $admin = $this->createAdmin();

        Garage::create([
            'kode' => 'C995',
            'tipe_garasi' => Garage::TIPE_MOBIL,
            'harga_harian' => 50000,
            'status' => Garage::STATUS_AVAILABLE,
        ]);

        // Sisa kuota hanya 4 (C996..C999), minta 5 harus ditolak
        $this->actingAs($admin)->post(route('admin.garages.store'), [
            'tipe_garasi' => Garage::TIPE_MOBIL,
            'jumlah' => 5,
            'harga_harian' => 50000,
        ])->assertSessionHasErrors('jumlah');

        // Minta 4 harus berhasil sampai C999
        $this->actingAs($admin)->post(route('admin.garages.store'), [
            'tipe_garasi' => Garage::TIPE_MOBIL,
            'jumlah' => 4,
            'harga_harian' => 50000,
        ])->assertRedirect(route('admin.garages.index'));

        $this->assertDatabaseHas('garages', ['kode' => 'C999']);
    }
}