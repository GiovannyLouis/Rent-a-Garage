<?php

namespace Tests\Feature;

use App\Models\Garage;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RentalAndExtensionTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role, string $email, string $nik): User
    {
        return User::create([
            'nik' => $nik,
            'nama' => ucfirst($role) . ' Test',
            'no_telp' => '081234567890',
            'email' => $email,
            'password' => 'password123',
            'role' => $role,
            'status' => User::STATUS_APPROVED,
        ]);
    }

    public function test_penyewa_can_complete_3_step_rental_and_rent_multiple_garages(): void
    {
        Storage::fake('public');
        $this->travelTo(Carbon::parse('2026-04-06'));

        $penyewa = $this->createUser(User::ROLE_PENYEWA, 'penyewa@test.com', '3201010101010010');

        $electricGarage = Garage::create([
            'kode' => 'E001',
            'tipe_garasi' => Garage::TIPE_MOBIL_LISTRIK,
            'harga_harian' => 60000,
            'harga_kwh' => 2500,
            'status' => Garage::STATUS_AVAILABLE,
        ]);

        $bikeGarage = Garage::create([
            'kode' => 'S001',
            'tipe_garasi' => Garage::TIPE_SEPEDA,
            'harga_harian' => 10000,
            'status' => Garage::STATUS_AVAILABLE,
        ]);

        // Sewa 1: Mobil Listrik di Garasi Mobil Listrik (3 hari, isi 50 kWh)
        $this->actingAs($penyewa)->post(route('penyewa.rentals.step1'), [
            'tipe_kendaraan' => Rental::KENDARAAN_MOBIL_LISTRIK,
            'plat_nomor' => 'B 1234 EV',
            'merek_tipe' => 'Hyundai Ioniq 5',
            'kapasitas_baterai' => 70,
        ])->assertRedirect(route('penyewa.rentals.step2'));

        $this->actingAs($penyewa)->post(route('penyewa.rentals.step2.store'), [
            'garage_id' => $electricGarage->id,
            'tipe_sewa' => Payment::SEWA_HARIAN,
            'jumlah_hari' => 3,
            'sisa_baterai' => 20,
        ])->assertRedirect(route('penyewa.rentals.step3'));

        $this->actingAs($penyewa)->get(route('penyewa.rentals.step3'))->assertOk();

        $this->actingAs($penyewa)->post(route('penyewa.rentals.store'), [
            'bukti_bayar' => UploadedFile::fake()->image('bukti1.jpg'),
        ])->assertRedirect(route('penyewa.rentals.index'));

        $this->assertSame(Garage::STATUS_USED, $electricGarage->fresh()->status);

        $rental1 = Rental::where('garage_id', $electricGarage->id)->firstOrFail();
        $this->assertSame('2026-04-06', $rental1->tanggal_mulai->toDateString());
        $this->assertSame('2026-04-09', $rental1->tanggal_selesai->toDateString());

        $this->assertDatabaseHas('payments', [
            'rental_id' => $rental1->id,
            'tujuan' => Payment::TUJUAN_SEWA_AWAL,
            'nominal' => 305000, // (3 * 60.000) + (50 * 2.500)
        ]);

        // Sewa 2 di waktu bersamaan: Sepeda (bulanan)
        $this->actingAs($penyewa)->post(route('penyewa.rentals.step1'), [
            'tipe_kendaraan' => Rental::KENDARAAN_SEPEDA,
            'merek_tipe' => 'Polygon Strattos',
        ])->assertRedirect(route('penyewa.rentals.step2'));

        $this->actingAs($penyewa)->post(route('penyewa.rentals.step2.store'), [
            'garage_id' => $bikeGarage->id,
            'tipe_sewa' => Payment::SEWA_BULANAN,
        ])->assertRedirect(route('penyewa.rentals.step3'));

        $this->actingAs($penyewa)->post(route('penyewa.rentals.store'), [
            'bukti_bayar' => UploadedFile::fake()->image('bukti2.jpg'),
        ])->assertRedirect(route('penyewa.rentals.index'));

        $this->assertSame(2, Rental::where('user_id', $penyewa->id)->count());
        $this->assertSame(Garage::STATUS_USED, $bikeGarage->fresh()->status);
    }

    public function test_incompatible_garage_and_excess_daily_days_are_rejected(): void
    {
        $this->travelTo(Carbon::parse('2026-04-06')); // April: maks harian = 29 hari

        $penyewa = $this->createUser(User::ROLE_PENYEWA, 'penyewa2@test.com', '3201010101010011');

        $carGarage = Garage::create([
            'kode' => 'C001',
            'tipe_garasi' => Garage::TIPE_MOBIL,
            'harga_harian' => 50000,
            'status' => Garage::STATUS_AVAILABLE,
        ]);

        $bikeGarage = Garage::create([
            'kode' => 'S001',
            'tipe_garasi' => Garage::TIPE_SEPEDA,
            'harga_harian' => 10000,
            'status' => Garage::STATUS_AVAILABLE,
        ]);

        // Sepeda motor tidak boleh masuk garasi mobil
        $this->actingAs($penyewa)->post(route('penyewa.rentals.step1'), [
            'tipe_kendaraan' => Rental::KENDARAAN_SEPEDA_MOTOR,
            'plat_nomor' => 'B 5555 MT',
            'merek_tipe' => 'Vario 160',
        ]);

        $this->actingAs($penyewa)->post(route('penyewa.rentals.step2.store'), [
            'garage_id' => $carGarage->id,
            'tipe_sewa' => Payment::SEWA_HARIAN,
            'jumlah_hari' => 5,
        ])->assertSessionHasErrors('garage_id');

        // Harian 30 hari pada bulan April (30 hari) harus ditolak karena maks = 29 hari
        $this->actingAs($penyewa)->post(route('penyewa.rentals.step2.store'), [
            'garage_id' => $bikeGarage->id,
            'tipe_sewa' => Payment::SEWA_HARIAN,
            'jumlah_hari' => 30,
        ])->assertSessionHasErrors('jumlah_hari');
    }

    public function test_admin_can_extend_rental_daily_and_monthly_without_creating_new_rental(): void
    {
        Storage::fake('public');
        $this->travelTo(Carbon::parse('2026-04-06'));

        $admin = $this->createUser(User::ROLE_ADMIN, 'admin@test.com', '0000000000000001');
        $penyewa = $this->createUser(User::ROLE_PENYEWA, 'penyewa3@test.com', '3201010101010012');

        $garage = Garage::create([
            'kode' => 'C002',
            'tipe_garasi' => Garage::TIPE_MOBIL,
            'harga_harian' => 50000,
            'status' => Garage::STATUS_USED,
        ]);

        $rental = Rental::create([
            'user_id' => $penyewa->id,
            'garage_id' => $garage->id,
            'tipe_kendaraan' => Rental::KENDARAAN_MOBIL,
            'plat_nomor' => 'B 7777 CC',
            'merek_tipe' => 'Honda Brio',
            'jumlah_hari' => 5,
            'tanggal_mulai' => '2026-04-06',
            'tanggal_selesai' => '2026-04-11',
        ]);

        Payment::create([
            'rental_id' => $rental->id,
            'tujuan' => Payment::TUJUAN_SEWA_AWAL,
            'tipe_sewa' => Payment::SEWA_HARIAN,
            'nominal' => 250000,
            'bukti_bayar' => 'bukti-bayar/awal.jpg',
        ]);

        // 1. Perpanjang harian melebihi batas (11 Apr -> 11 Mei = 30 hari, maks harian = 29) ditolak
        $this->actingAs($admin)->post(route('admin.rentals.extend', $rental->id), [
            'tipe_sewa' => Payment::SEWA_HARIAN,
            'jumlah_hari' => 30,
            'bukti_bayar' => UploadedFile::fake()->image('ext-fail.jpg'),
        ])->assertSessionHasErrors('jumlah_hari');

        // 2. Perpanjang harian 5 hari (11 Apr -> 16 Apr) berhasil
        $this->actingAs($admin)->post(route('admin.rentals.extend', $rental->id), [
            'tipe_sewa' => Payment::SEWA_HARIAN,
            'jumlah_hari' => 5,
            'bukti_bayar' => UploadedFile::fake()->image('ext-harian.jpg'),
        ])->assertRedirect(route('admin.rentals.index'));

        $rental->refresh();
        $this->assertSame('2026-04-16', $rental->tanggal_selesai->toDateString());
        $this->assertSame(10, $rental->jumlah_hari);

        // 3. Perpanjang bulanan (16 Apr -> 16 Mei) berhasil dengan tarif 25x harian
        $this->actingAs($admin)->post(route('admin.rentals.extend', $rental->id), [
            'tipe_sewa' => Payment::SEWA_BULANAN,
            'bukti_bayar' => UploadedFile::fake()->image('ext-bulanan.jpg'),
        ])->assertRedirect(route('admin.rentals.index'));

        $rental->refresh();
        $this->assertSame('2026-05-16', $rental->tanggal_selesai->toDateString());
        $this->assertSame(40, $rental->jumlah_hari);

        // Tetap 1 row di rentals, tapi ada 3 row di payments (1 sewa_awal + 2 perpanjangan)
        $this->assertSame(1, Rental::count());
        $this->assertSame(3, Payment::where('rental_id', $rental->id)->count());
        $this->assertDatabaseHas('payments', [
            'rental_id' => $rental->id,
            'tujuan' => Payment::TUJUAN_PERPANJANGAN,
            'tipe_sewa' => Payment::SEWA_BULANAN,
            'nominal' => 1250000,
        ]);
    }
}