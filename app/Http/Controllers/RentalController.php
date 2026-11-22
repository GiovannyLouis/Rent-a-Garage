<?php

namespace App\Http\Controllers;

use App\Models\Garage;
use App\Models\Payment;
use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

class RentalController extends Controller
{
    /**
     * Peta kompatibilitas tipe kendaraan terhadap tipe garasi yang diizinkan.
     *
     * @var array<string, list<string>>
     */
    private const VEHICLE_GARAGE_MAP = [
        Rental::KENDARAAN_SEPEDA => [Garage::TIPE_SEPEDA],
        Rental::KENDARAAN_SEPEDA_MOTOR => [Garage::TIPE_SEPEDA],
        Rental::KENDARAAN_MOBIL => [Garage::TIPE_MOBIL],
        Rental::KENDARAAN_MOBIL_LISTRIK => [Garage::TIPE_MOBIL, Garage::TIPE_MOBIL_LISTRIK],
    ];

    /**
     * Menampilkan daftar penyewaan milik penyewa yang sedang login.
     */
    public function index(): View
    {
        $rentals = Rental::with(['garage', 'payments'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('penyewa.rentals.index', compact('rentals'));
    }

    /**
     * Step 1: Menampilkan form detail kendaraan.
     */
    public function create(): View
    {
        $draft = session('rental_draft', []);

        return view('penyewa.rentals.create', compact('draft'));
    }

    /**
     * Memvalidasi Step 1 (Detail Kendaraan) dan menyimpan ke session.
     */
    public function storeStep1(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tipe_kendaraan' => ['required', Rule::in(array_keys(self::VEHICLE_GARAGE_MAP))],
            'plat_nomor' => [
                'nullable',
                'required_unless:tipe_kendaraan,' . Rental::KENDARAAN_SEPEDA,
                'string',
                'max:20',
            ],
            'merek_tipe' => ['required', 'string', 'max:255'],
            'kapasitas_baterai' => [
                'nullable',
                'required_if:tipe_kendaraan,' . Rental::KENDARAAN_MOBIL_LISTRIK,
                'numeric',
                'gt:0',
            ],
        ], [
            'tipe_kendaraan.required' => 'Tipe kendaraan wajib dipilih.',
            'plat_nomor.required_unless' => 'Nomor plat wajib diisi selain untuk sepeda.',
            'merek_tipe.required' => 'Merek dan tipe kendaraan wajib diisi.',
            'kapasitas_baterai.required_if' => 'Kapasitas listrik baterai (kWh) wajib diisi untuk mobil listrik.',
            'kapasitas_baterai.gt' => 'Kapasitas baterai harus lebih dari 0 kWh.',
        ]);

        $tipeKendaraan = $validated['tipe_kendaraan'];

        session([
            'rental_draft' => [
                'tipe_kendaraan' => $tipeKendaraan,
                'plat_nomor' => $tipeKendaraan === Rental::KENDARAAN_SEPEDA
                    ? null
                    : strtoupper(trim((string) $validated['plat_nomor'])),
                'merek_tipe' => trim($validated['merek_tipe']),
                'kapasitas_baterai' => $tipeKendaraan === Rental::KENDARAAN_MOBIL_LISTRIK
                    ? (float) $validated['kapasitas_baterai']
                    : null,
            ],
        ]);

        return redirect()->route('penyewa.rentals.step2');
    }

    /**
     * Step 2: Menampilkan daftar garasi yang sesuai & tersedia hari ini, serta pilihan periode sewa.
     */
    public function step2(): View|RedirectResponse
    {
        $draft = session('rental_draft');

        if (! $draft || empty($draft['tipe_kendaraan'])) {
            return redirect()->route('penyewa.rentals.create');
        }

        $allowedGarageTypes = self::VEHICLE_GARAGE_MAP[$draft['tipe_kendaraan']];

        $availableGarages = Garage::whereIn('tipe_garasi', $allowedGarageTypes)
            ->where('status', Garage::STATUS_AVAILABLE)
            ->orderBy('kode')
            ->get();

        $tanggalMulai = now()->toDateString();

        return view('penyewa.rentals.step2', compact('draft', 'availableGarages', 'tanggalMulai'));
    }

    /**
     * Memvalidasi Step 2 (Garasi & Periode Sewa) dan menyimpan ke session.
     */
    public function storeStep2(Request $request): RedirectResponse
    {
        $draft = session('rental_draft');

        if (! $draft || empty($draft['tipe_kendaraan'])) {
            return redirect()->route('penyewa.rentals.create');
        }

        $validated = $request->validate([
            'garage_id' => ['required', 'integer', 'exists:garages,id'],
            'tipe_sewa' => ['required', Rule::in([Payment::SEWA_HARIAN, Payment::SEWA_BULANAN])],
            'jumlah_hari' => [
                'nullable',
                'required_if:tipe_sewa,' . Payment::SEWA_HARIAN,
                'integer',
                'min:1',
                'max:30',
            ],
            'sisa_baterai' => ['nullable', 'numeric', 'gte:0'],
        ], [
            'garage_id.required' => 'Silakan pilih garasi yang tersedia.',
            'tipe_sewa.required' => 'Tipe sewa wajib dipilih.',
            'jumlah_hari.required_if' => 'Jumlah hari wajib diisi untuk sewa harian.',
            'jumlah_hari.min' => 'Jumlah hari minimal 1 hari.',
            'jumlah_hari.max' => 'Jumlah hari untuk sewa harian maksimal 30 hari.',
            'sisa_baterai.gte' => 'Sisa baterai tidak boleh negatif.',
        ]);

        $garage = Garage::where('status', Garage::STATUS_AVAILABLE)->find($validated['garage_id']);

        if (! $garage) {
            return back()->withInput()->withErrors(['garage_id' => 'Garasi yang dipilih sudah tidak tersedia.']);
        }

        $allowedGarageTypes = self::VEHICLE_GARAGE_MAP[$draft['tipe_kendaraan']];
        if (! in_array($garage->tipe_garasi, $allowedGarageTypes, true)) {
            return back()->withInput()->withErrors(['garage_id' => 'Garasi tidak sesuai dengan tipe kendaraan Anda.']);
        }

        $sisaBaterai = null;

        if (
            $draft['tipe_kendaraan'] === Rental::KENDARAAN_MOBIL_LISTRIK
            && $garage->tipe_garasi === Garage::TIPE_MOBIL_LISTRIK
        ) {
            if ($validated['sisa_baterai'] === null || $validated['sisa_baterai'] === '') {
                return back()->withInput()->withErrors([
                    'sisa_baterai' => 'Sisa baterai (kWh) wajib diisi jika memilih Garasi Mobil Listrik.',
                ]);
            }

            if ((float) $validated['sisa_baterai'] > (float) $draft['kapasitas_baterai']) {
                return back()->withInput()->withErrors([
                    'sisa_baterai' => "Sisa baterai tidak boleh melebihi kapasitas baterai ({$draft['kapasitas_baterai']} kWh).",
                ]);
            }

            $sisaBaterai = (float) $validated['sisa_baterai'];
        }

        $draft['garage_id'] = $garage->id;
        $draft['tipe_sewa'] = $validated['tipe_sewa'];
        $draft['jumlah_hari'] = $validated['tipe_sewa'] === Payment::SEWA_HARIAN
            ? (int) $validated['jumlah_hari']
            : 0;
        $draft['sisa_baterai'] = $sisaBaterai;

        session(['rental_draft' => $draft]);

        return redirect()->route('penyewa.rentals.step3');
    }

    /**
     * Step 3: Menampilkan tanggal selesai, rincian biaya, dan form upload bukti bayar.
     */
    public function step3(): View|RedirectResponse
    {
        $draft = session('rental_draft');

        if (! $draft || empty($draft['garage_id'])) {
            return redirect()->route('penyewa.rentals.create');
        }

        $garage = Garage::where('status', Garage::STATUS_AVAILABLE)->find($draft['garage_id']);

        if (! $garage) {
            return redirect()->route('penyewa.rentals.step2')
                ->withErrors(['garage_id' => 'Garasi sudah tidak tersedia, silakan pilih garasi lain.']);
        }

        $tanggalMulai = now()->toDateString();
        $tanggalSelesai = $this->calculateEndDate($draft['tipe_sewa'], $tanggalMulai, $draft['jumlah_hari']);

        $biayaSewa = $draft['tipe_sewa'] === Payment::SEWA_BULANAN
            ? $garage->getHargaBulanan()
            : $garage->harga_harian * $draft['jumlah_hari'];

        $totalBiaya = $this->calculateInitialCost(
            $garage,
            $draft['tipe_sewa'],
            $draft['jumlah_hari'],
            (float) ($draft['kapasitas_baterai'] ?? 0),
            (float) ($draft['sisa_baterai'] ?? 0),
            $draft['tipe_kendaraan']
        );

        $biayaListrik = $totalBiaya - $biayaSewa;

        return view('penyewa.rentals.step3', compact(
            'draft',
            'garage',
            'tanggalMulai',
            'tanggalSelesai',
            'biayaSewa',
            'biayaListrik',
            'totalBiaya'
        ));
    }

    /**
     * Menyimpan transaksi sewa, bukti pembayaran, dan mengubah status garasi menjadi 'used'.
     */
    public function store(Request $request): RedirectResponse
    {
        $draft = session('rental_draft');

        if (! $draft || empty($draft['garage_id'])) {
            return redirect()->route('penyewa.rentals.create');
        }

        $validated = $request->validate([
            'bukti_bayar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'bukti_bayar.required' => 'Bukti pelunasan biaya wajib diupload.',
            'bukti_bayar.image' => 'Bukti pelunasan harus berupa gambar (JPG/PNG/WEBP).',
            'bukti_bayar.max' => 'Ukuran gambar maksimal 2 MB.',
        ]);

        $garage = Garage::findOrFail($draft['garage_id']);

        $tanggalMulai = now()->toDateString();
        $tanggalSelesai = $this->calculateEndDate($draft['tipe_sewa'], $tanggalMulai, $draft['jumlah_hari']);

        $jumlahHariTersimpan = $draft['tipe_sewa'] === Payment::SEWA_HARIAN
            ? $draft['jumlah_hari']
            : (int) Carbon::parse($tanggalMulai)->diffInDays(Carbon::parse($tanggalSelesai));

        $totalBiaya = $this->calculateInitialCost(
            $garage,
            $draft['tipe_sewa'],
            $draft['jumlah_hari'],
            (float) ($draft['kapasitas_baterai'] ?? 0),
            (float) ($draft['sisa_baterai'] ?? 0),
            $draft['tipe_kendaraan']
        );

        $buktiPath = $validated['bukti_bayar']->store('bukti-bayar', 'public');

        try {
            DB::transaction(function () use (
                $garage,
                $draft,
                $jumlahHariTersimpan,
                $tanggalMulai,
                $tanggalSelesai,
                $totalBiaya,
                $buktiPath
            ): void {
                $updated = Garage::where('id', $garage->id)
                    ->where('status', Garage::STATUS_AVAILABLE)
                    ->update(['status' => Garage::STATUS_USED]);

                if ($updated === 0) {
                    throw ValidationException::withMessages([
                        'garage_id' => 'Maaf, garasi baru saja disewa orang lain. Silakan pilih garasi lain.',
                    ]);
                }

                $rental = Rental::create([
                    'user_id' => Auth::id(),
                    'garage_id' => $garage->id,
                    'tipe_kendaraan' => $draft['tipe_kendaraan'],
                    'plat_nomor' => $draft['plat_nomor'],
                    'merek_tipe' => $draft['merek_tipe'],
                    'kapasitas_baterai' => $draft['kapasitas_baterai'],
                    'sisa_baterai' => $draft['sisa_baterai'],
                    'jumlah_hari' => $jumlahHariTersimpan,
                    'tanggal_mulai' => $tanggalMulai,
                    'tanggal_selesai' => $tanggalSelesai,
                ]);

                Payment::create([
                    'rental_id' => $rental->id,
                    'tujuan' => Payment::TUJUAN_SEWA_AWAL,
                    'tipe_sewa' => $draft['tipe_sewa'],
                    'nominal' => $totalBiaya,
                    'bukti_bayar' => $buktiPath,
                ]);
            });
        } catch (Throwable $e) {
            Storage::disk('public')->delete($buktiPath);
            throw $e;
        }

        session()->forget('rental_draft');

        return redirect()
            ->route('penyewa.rentals.index')
            ->with('success', "Sewa garasi {$garage->kode} berhasil disimpan! Total biaya: Rp " . number_format($totalBiaya, 0, ',', '.'));
    }

    /**
     * Menghitung tanggal selesai sewa (Harian: +N hari, Bulanan: tanggal sama bulan depan tanpa overflow).
     *
     * @throws InvalidArgumentException Jika tipe sewa tidak dikenali.
     */
    public function calculateEndDate(string $tipeSewa, string $tanggalMulai, int $jumlahHari): string
    {
        $startDate = Carbon::parse($tanggalMulai);

        if ($tipeSewa === Payment::SEWA_BULANAN) {
            return $startDate->copy()->addMonthNoOverflow()->toDateString();
        }

        if ($tipeSewa === Payment::SEWA_HARIAN) {
            return $startDate->copy()->addDays($jumlahHari)->toDateString();
        }

        throw new InvalidArgumentException("Tipe sewa '{$tipeSewa}' tidak valid.");
    }

    /**
     * Menghitung total biaya sewa awal (biaya sewa garasi + biaya isi listrik penuh jika berlaku).
     */
    public function calculateInitialCost(
        Garage $garage,
        string $tipeSewa,
        int $jumlahHari,
        float $kapasitasBaterai,
        float $sisaBaterai,
        string $tipeKendaraan
    ): int {
        $biayaSewa = $tipeSewa === Payment::SEWA_BULANAN
            ? $garage->getHargaBulanan()
            : $garage->harga_harian * $jumlahHari;

        $biayaListrik = 0.0;

        if (
            $tipeKendaraan === Rental::KENDARAAN_MOBIL_LISTRIK
            && $garage->tipe_garasi === Garage::TIPE_MOBIL_LISTRIK
            && $garage->harga_kwh !== null
        ) {
            $kwhDiisi = max(0.0, $kapasitasBaterai - $sisaBaterai);
            $biayaListrik = $kwhDiisi * $garage->harga_kwh;
        }

        return (int) round($biayaSewa + $biayaListrik);
    }
}