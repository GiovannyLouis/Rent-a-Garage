<?php

namespace Tests\Unit;

use App\Http\Controllers\ReportController;
use App\Models\Garage;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_queries_filter_correctly_by_month_day_type_and_user(): void
    {
        $user1 = User::create([
            'nik' => '1111111111111111',
            'nama' => 'Penyewa Satu',
            'no_telp' => '081111111111',
            'email' => 'satu@test.com',
            'password' => 'password',
            'role' => User::ROLE_PENYEWA,
            'status' => User::STATUS_APPROVED,
        ]);

        $user2 = User::create([
            'nik' => '2222222222222222',
            'nama' => 'Penyewa Dua',
            'no_telp' => '082222222222',
            'email' => 'dua@test.com',
            'password' => 'password',
            'role' => User::ROLE_PENYEWA,
            'status' => User::STATUS_APPROVED,
        ]);

        $bikeGarage = Garage::create([
            'kode' => 'S001',
            'tipe_garasi' => Garage::TIPE_SEPEDA,
            'harga_harian' => 10000,
            'status' => Garage::STATUS_USED,
        ]);

        $carGarage = Garage::create([
            'kode' => 'C001',
            'tipe_garasi' => Garage::TIPE_MOBIL,
            'harga_harian' => 50000,
            'status' => Garage::STATUS_USED,
        ]);

        Rental::create([
            'user_id' => $user1->id,
            'garage_id' => $bikeGarage->id,
            'tipe_kendaraan' => Rental::KENDARAAN_SEPEDA,
            'plat_nomor' => null,
            'merek_tipe' => 'Polygon',
            'jumlah_hari' => 3,
            'tanggal_mulai' => '2026-04-06',
            'tanggal_selesai' => '2026-04-09',
        ]);

        Rental::create([
            'user_id' => $user2->id,
            'garage_id' => $carGarage->id,
            'tipe_kendaraan' => Rental::KENDARAAN_MOBIL,
            'plat_nomor' => 'B 9999 AA',
            'merek_tipe' => 'Honda Civic',
            'jumlah_hari' => 5,
            'tanggal_mulai' => '2026-04-10',
            'tanggal_selesai' => '2026-04-15',
        ]);

        Rental::create([
            'user_id' => $user1->id,
            'garage_id' => $carGarage->id,
            'tipe_kendaraan' => Rental::KENDARAAN_MOBIL,
            'plat_nomor' => 'B 8888 BB',
            'merek_tipe' => 'Toyota Yaris',
            'jumlah_hari' => 2,
            'tanggal_mulai' => '2026-05-02',
            'tanggal_selesai' => '2026-05-04',
        ]);

        $report = new ReportController();

        $this->assertCount(2, $report->byMonth(2026, 4));
        $this->assertCount(1, $report->byMonth(2026, 5));

        $this->assertCount(1, $report->byDay(2026, 4, 6));
        $this->assertCount(0, $report->byDay(2026, 2, 30)); // Tanggal tidak valid

        $this->assertCount(1, $report->byGarageType(Garage::TIPE_SEPEDA));
        $this->assertCount(2, $report->byGarageType(Garage::TIPE_MOBIL));

        $this->assertCount(2, $report->byUser($user1->id));
        $this->assertCount(1, $report->byUser($user2->id));
    }
}