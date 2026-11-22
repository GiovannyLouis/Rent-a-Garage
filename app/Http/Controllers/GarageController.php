<?php

namespace App\Http\Controllers;

use App\Models\Garage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;
use OverflowException;

class GarageController extends Controller
{
    public const MAX_GARAGE_PER_TYPE = 999;

    /**
     * Menampilkan halaman manajemen garasi (form batch di atas & daftar garasi di bawah).
     */
    public function index(): View
    {
        $garages = Garage::orderBy('kode')->paginate(15);

        $summary = [
            Garage::TIPE_SEPEDA => Garage::where('tipe_garasi', Garage::TIPE_SEPEDA)->count(),
            Garage::TIPE_MOBIL => Garage::where('tipe_garasi', Garage::TIPE_MOBIL)->count(),
            Garage::TIPE_MOBIL_LISTRIK => Garage::where('tipe_garasi', Garage::TIPE_MOBIL_LISTRIK)->count(),
        ];

        $remainingQuota = [
            Garage::TIPE_SEPEDA => self::MAX_GARAGE_PER_TYPE - $this->getLastSequenceNumber(Garage::TIPE_SEPEDA),
            Garage::TIPE_MOBIL => self::MAX_GARAGE_PER_TYPE - $this->getLastSequenceNumber(Garage::TIPE_MOBIL),
            Garage::TIPE_MOBIL_LISTRIK => self::MAX_GARAGE_PER_TYPE - $this->getLastSequenceNumber(Garage::TIPE_MOBIL_LISTRIK),
        ];

        return view('admin.garages.index', compact('garages', 'summary', 'remainingQuota'));
    }

    /**
     * Menampilkan halaman pembuatan garasi (diarahkan ke tampilan 1 halaman index).
     */
    public function createBatch(): View
    {
        return $this->index();
    }

    /**
     * Menyimpan sejumlah garasi baru secara berkelompok dengan kode otomatis.
     */
    public function storeBatch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tipe_garasi' => [
                'required',
                Rule::in([
                    Garage::TIPE_SEPEDA,
                    Garage::TIPE_MOBIL,
                    Garage::TIPE_MOBIL_LISTRIK,
                ]),
            ],
            'jumlah' => ['required', 'integer', 'min:1', 'max:' . self::MAX_GARAGE_PER_TYPE],
            'harga_harian' => ['required', 'integer', 'min:1'],
            'harga_kwh' => [
                'nullable',
                'required_if:tipe_garasi,' . Garage::TIPE_MOBIL_LISTRIK,
                'integer',
                'min:1',
            ],
        ], [
            'tipe_garasi.required' => 'Tipe garasi wajib dipilih.',
            'tipe_garasi.in' => 'Tipe garasi tidak valid.',
            'jumlah.required' => 'Jumlah garasi wajib diisi.',
            'jumlah.integer' => 'Jumlah garasi harus berupa angka bulat.',
            'jumlah.min' => 'Jumlah garasi minimal 1.',
            'jumlah.max' => 'Jumlah garasi maksimal 999.',
            'harga_harian.required' => 'Harga sewa harian wajib diisi.',
            'harga_harian.integer' => 'Harga sewa harian harus berupa angka.',
            'harga_harian.min' => 'Harga sewa harian minimal Rp 1.',
            'harga_kwh.required_if' => 'Harga isi listrik per kWh wajib diisi untuk garasi Mobil Listrik.',
            'harga_kwh.integer' => 'Harga isi listrik per kWh harus berupa angka.',
            'harga_kwh.min' => 'Harga isi listrik per kWh minimal Rp 1.',
        ]);

        $tipeGarasi = $validated['tipe_garasi'];
        $jumlah = (int) $validated['jumlah'];
        $hargaHarian = (int) $validated['harga_harian'];
        $hargaKwh = $tipeGarasi === Garage::TIPE_MOBIL_LISTRIK
            ? (int) $validated['harga_kwh']
            : null;

        // Validasi kuota maksimal 999 per tipe garasi (kumulatif)
        $lastNumber = $this->getLastSequenceNumber($tipeGarasi);
        $sisaKuota = self::MAX_GARAGE_PER_TYPE - $lastNumber;

        if ($jumlah > $sisaKuota) {
            return back()
                ->withInput()
                ->withErrors([
                    'jumlah' => "Jumlah melebihi batas maksimal 999 unit untuk tipe garasi ini. Sisa kuota tersedia: {$sisaKuota}.",
                ]);
        }

        $createdCodes = DB::transaction(function () use ($tipeGarasi, $jumlah, $hargaHarian, $hargaKwh): array {
            $codes = [];

            for ($i = 0; $i < $jumlah; $i++) {
                $kode = $this->generateCode($tipeGarasi);

                Garage::create([
                    'kode' => $kode,
                    'tipe_garasi' => $tipeGarasi,
                    'harga_harian' => $hargaHarian,
                    'harga_kwh' => $hargaKwh,
                    'status' => Garage::STATUS_AVAILABLE,
                ]);

                $codes[] = $kode;
            }

            return $codes;
        });

        $firstCode = $createdCodes[0];
        $lastCode = $createdCodes[count($createdCodes) - 1];
        $rangeInfo = $jumlah === 1 ? $firstCode : "{$firstCode} s/d {$lastCode}";

        return redirect()
            ->route('admin.garages.index')
            ->with('success', "Berhasil membuat {$jumlah} garasi baru ({$rangeInfo}).");
    }

    /**
     * Menghasilkan kode garasi berikutnya sesuai tipe (S001, C001, E001, dst).
     *
     * @throws InvalidArgumentException Jika tipe garasi tidak dikenali.
     * @throws OverflowException Jika nomor urut sudah melebihi 999.
     */
    public function generateCode(string $tipeGarasi): string
    {
        $prefix = match ($tipeGarasi) {
            Garage::TIPE_SEPEDA => 'S',
            Garage::TIPE_MOBIL => 'C',
            Garage::TIPE_MOBIL_LISTRIK => 'E',
            default => throw new InvalidArgumentException("Tipe garasi '{$tipeGarasi}' tidak valid."),
        };

        $nextNumber = $this->getLastSequenceNumber($tipeGarasi) + 1;

        if ($nextNumber > self::MAX_GARAGE_PER_TYPE) {
            throw new OverflowException("Jumlah garasi tipe '{$tipeGarasi}' sudah mencapai batas maksimal 999.");
        }

        return $prefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Mengambil angka urutan terakhir dari kode garasi berdasarkan tipenya.
     */
    private function getLastSequenceNumber(string $tipeGarasi): int
    {
        $lastGarage = Garage::where('tipe_garasi', $tipeGarasi)
            ->orderByDesc('kode')
            ->first();

        if (! $lastGarage) {
            return 0;
        }

        return (int) substr($lastGarage->kode, 1);
    }
}