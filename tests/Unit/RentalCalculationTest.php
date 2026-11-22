<?php

namespace Tests\Unit;

use App\Http\Controllers\RentalController;
use App\Models\Garage;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class RentalCalculationTest extends TestCase
{
    use RefreshDatabase;

    private RentalController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new RentalController();
    }

    public function test_calculate_end_date_for_daily_rental(): void
    {
        $this->assertSame(
            '2026-03-25',
            $this->controller->calculateEndDate(Payment::SEWA_HARIAN, '2026-03-15', 10)
        );
    }

    public function test_calculate_end_date_for_monthly_rental_without_overflow(): void
    {
        // Kasus normal
        $this->assertSame(
            '2026-03-15',
            $this->controller->calculateEndDate(Payment::SEWA_BULANAN, '2026-02-15', 0)
        );
        $this->assertSame(
            '2026-04-15',
            $this->controller->calculateEndDate(Payment::SEWA_BULANAN, '2026-03-15', 0)
        );

        // Edge case tanggal akhir bulan (tidak overflow ke bulan berikutnya)
        $this->assertSame(
            '2026-04-30',
            $this->controller->calculateEndDate(Payment::SEWA_BULANAN, '2026-03-31', 0)
        );
        $this->assertSame(
            '2026-02-28',
            $this->controller->calculateEndDate(Payment::SEWA_BULANAN, '2026-01-31', 0)
        );
        // Tahun kabisat
        $this->assertSame(
            '2024-02-29',
            $this->controller->calculateEndDate(Payment::SEWA_BULANAN, '2024-01-31', 0)
        );
    }

    public function test_calculate_end_date_throws_exception_for_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->controller->calculateEndDate('tahunan', '2026-04-06', 1);
    }

    public function test_get_max_daily_days_follows_monthly_span_minus_one(): void
    {
        $this->assertSame(30, $this->controller->getMaxDailyDays('2026-03-15')); // 31 - 1
        $this->assertSame(29, $this->controller->getMaxDailyDays('2026-04-15')); // 30 - 1
        $this->assertSame(27, $this->controller->getMaxDailyDays('2026-01-31')); // 28 - 1
        $this->assertSame(28, $this->controller->getMaxDailyDays('2024-01-31')); // 29 - 1 (kabisat)
    }

    public function test_calculate_initial_cost_for_regular_and_electric_garages(): void
    {
        $carGarage = new Garage([
            'tipe_garasi' => Garage::TIPE_MOBIL,
            'harga_harian' => 50000,
            'harga_kwh' => null,
        ]);

        $electricGarage = new Garage([
            'tipe_garasi' => Garage::TIPE_MOBIL_LISTRIK,
            'harga_harian' => 60000,
            'harga_kwh' => 2500,
        ]);

        // Sewa harian mobil biasa (4 x 50.000)
        $this->assertSame(
            200000,
            $this->controller->calculateInitialCost($carGarage, Payment::SEWA_HARIAN, 4, 0, 0, Rental::KENDARAAN_MOBIL)
        );

        // Sewa bulanan mobil biasa (25 x 50.000)
        $this->assertSame(
            1250000,
            $this->controller->calculateInitialCost($carGarage, Payment::SEWA_BULANAN, 0, 0, 0, Rental::KENDARAAN_MOBIL)
        );

        // Mobil listrik di garasi mobil listrik: (2 x 60.000) + ((70 - 20) x 2.500) = 120.000 + 125.000 = 245.000
        $this->assertSame(
            245000,
            $this->controller->calculateInitialCost($electricGarage, Payment::SEWA_HARIAN, 2, 70, 20, Rental::KENDARAAN_MOBIL_LISTRIK)
        );

        // Mobil listrik di garasi mobil biasa: tidak ada biaya isi listrik
        $this->assertSame(
            100000,
            $this->controller->calculateInitialCost($carGarage, Payment::SEWA_HARIAN, 2, 70, 20, Rental::KENDARAAN_MOBIL_LISTRIK)
        );
    }

    public function test_calculate_extension_cost_for_daily_and_monthly(): void
    {
        $user = User::create([
            'nik' => '1234567890123456',
            'nama' => 'Budi',
            'no_telp' => '081234567890',
            'email' => 'budi@test.com',
            'password' => 'password123',
            'role' => User::ROLE_PENYEWA,
            'status' => User::STATUS_APPROVED,
        ]);

        $garage = Garage::create([
            'kode' => 'C001',
            'tipe_garasi' => Garage::TIPE_MOBIL,
            'harga_harian' => 50000,
            'harga_kwh' => null,
            'status' => Garage::STATUS_USED,
        ]);

        $rental = Rental::create([
            'user_id' => $user->id,
            'garage_id' => $garage->id,
            'tipe_kendaraan' => Rental::KENDARAAN_MOBIL,
            'plat_nomor' => 'B 1234 XX',
            'merek_tipe' => 'Toyota Avanza',
            'kapasitas_baterai' => null,
            'sisa_baterai' => null,
            'jumlah_hari' => 5,
            'tanggal_mulai' => '2026-04-06',
            'tanggal_selesai' => '2026-04-11',
        ]);

        $this->assertSame(350000, $this->controller->calculateExtensionCost($rental, Payment::SEWA_HARIAN, 7));
        $this->assertSame(1250000, $this->controller->calculateExtensionCost($rental, Payment::SEWA_BULANAN, 0));
    }
}