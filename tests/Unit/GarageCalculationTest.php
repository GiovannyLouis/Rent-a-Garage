<?php

namespace Tests\Unit;

use App\Http\Controllers\GarageController;
use App\Models\Garage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use OverflowException;
use Tests\TestCase;

class GarageCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_monthly_price_is_25_times_daily_price(): void
    {
        $garage = new Garage([
            'tipe_garasi' => Garage::TIPE_MOBIL,
            'harga_harian' => 40000,
        ]);

        $this->assertSame(1000000, $garage->getHargaBulanan());
    }

    public function test_generate_code_follows_prefix_and_incremental_sequence(): void
    {
        $controller = new GarageController();

        $this->assertSame('S001', $controller->generateCode(Garage::TIPE_SEPEDA));
        $this->assertSame('C001', $controller->generateCode(Garage::TIPE_MOBIL));
        $this->assertSame('E001', $controller->generateCode(Garage::TIPE_MOBIL_LISTRIK));

        Garage::create([
            'kode' => 'C009',
            'tipe_garasi' => Garage::TIPE_MOBIL,
            'harga_harian' => 50000,
            'harga_kwh' => null,
            'status' => Garage::STATUS_AVAILABLE,
        ]);

        $this->assertSame('C010', $controller->generateCode(Garage::TIPE_MOBIL));
    }

    public function test_generate_code_throws_exception_for_invalid_garage_type(): void
    {
        $controller = new GarageController();

        $this->expectException(InvalidArgumentException::class);
        $controller->generateCode('pesawat');
    }

    public function test_generate_code_throws_overflow_when_reaching_999(): void
    {
        Garage::create([
            'kode' => 'S999',
            'tipe_garasi' => Garage::TIPE_SEPEDA,
            'harga_harian' => 10000,
            'harga_kwh' => null,
            'status' => Garage::STATUS_AVAILABLE,
        ]);

        $controller = new GarageController();

        $this->expectException(OverflowException::class);
        $controller->generateCode(Garage::TIPE_SEPEDA);
    }
}