<?php

namespace App\Http\Controllers;

use App\Models\Garage;
use App\Models\Rental;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Daftar nama bulan dalam Bahasa Indonesia.
     *
     * @var array<int, string>
     */
    private const MONTH_NAMES = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    /**
     * Menampilkan halaman laporan sewa garasi sesuai tab filter yang dipilih.
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'month');
        if (! in_array($tab, ['month', 'day', 'garage_type', 'user'], true)) {
            $tab = 'month';
        }

        $year = (int) $request->query('year', now()->year);
        $month = $request->query('month') ? (int) $request->query('month') : null;
        $day = $request->query('day') ? (int) $request->query('day') : null;
        $tipeGarasi = $request->query('tipe_garasi');
        $userId = $request->query('user_id') ? (int) $request->query('user_id') : null;

        $monthNames = self::MONTH_NAMES;
        $summaryRows = [];
        $rentals = null;
        $detailTitle = '';

        $penyewaList = User::where('role', User::ROLE_PENYEWA)
            ->orderBy('nama')
            ->get(['id', 'nama', 'nik']);

        if ($tab === 'month') {
            $yearRentals = Rental::with('payments')
                ->whereBetween('tanggal_mulai', ["{$year}-01-01", "{$year}-12-31"])
                ->get()
                ->groupBy(fn (Rental $r): int => (int) $r->tanggal_mulai->month);

            foreach ($monthNames as $m => $label) {
                $group = $yearRentals->get($m, new Collection());
                $summaryRows[] = [
                    'month' => $m,
                    'label' => $label,
                    'count' => $group->count(),
                    'total' => $group->sum(fn (Rental $r): int => (int) $r->payments->sum('nominal')),
                ];
            }

            if ($month !== null && $month >= 1 && $month <= 12) {
                $rentals = $this->byMonth($year, $month);
                $detailTitle = "Detail Sewa Bulan {$monthNames[$month]} {$year}";
            }
        } elseif ($tab === 'day') {
            $month = ($month !== null && $month >= 1 && $month <= 12) ? $month : (int) now()->month;
            $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
            $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

            $monthRentals = Rental::with('payments')
                ->whereBetween('tanggal_mulai', [$startOfMonth, $endOfMonth])
                ->orderBy('tanggal_mulai')
                ->get()
                ->groupBy(fn (Rental $r): int => (int) $r->tanggal_mulai->day);

            foreach ($monthRentals as $d => $group) {
                $summaryRows[] = [
                    'day' => $d,
                    'date' => Carbon::create($year, $month, $d)->format('d/m/Y'),
                    'count' => $group->count(),
                    'total' => $group->sum(fn (Rental $r): int => (int) $r->payments->sum('nominal')),
                ];
            }

            if ($day !== null && checkdate($month, $day, $year)) {
                $rentals = $this->byDay($year, $month, $day);
                $detailTitle = 'Detail Sewa Tanggal ' . Carbon::create($year, $month, $day)->format('d/m/Y');
            }
        } elseif ($tab === 'garage_type') {
            $types = [
                Garage::TIPE_SEPEDA => 'Garasi Sepeda (S)',
                Garage::TIPE_MOBIL => 'Garasi Mobil (C)',
                Garage::TIPE_MOBIL_LISTRIK => 'Garasi Mobil Listrik (E)',
            ];

            $allRentals = Rental::with(['garage', 'payments'])->get();

            foreach ($types as $key => $label) {
                $group = $allRentals->filter(fn (Rental $r): bool => $r->garage->tipe_garasi === $key);
                $summaryRows[] = [
                    'tipe_garasi' => $key,
                    'label' => $label,
                    'count' => $group->count(),
                    'total' => $group->sum(fn (Rental $r): int => (int) $r->payments->sum('nominal')),
                ];
            }

            if ($tipeGarasi && array_key_exists($tipeGarasi, $types)) {
                $rentals = $this->byGarageType($tipeGarasi);
                $detailTitle = "Detail Sewa {$types[$tipeGarasi]}";
            }
        } elseif ($tab === 'user') {
            $allRentals = Rental::with(['user', 'payments'])
                ->get()
                ->groupBy('user_id');

            foreach ($penyewaList as $penyewa) {
                $group = $allRentals->get($penyewa->id, new Collection());
                $summaryRows[] = [
                    'user_id' => $penyewa->id,
                    'nama' => $penyewa->nama,
                    'nik' => $penyewa->nik,
                    'count' => $group->count(),
                    'total' => $group->sum(fn (Rental $r): int => (int) $r->payments->sum('nominal')),
                ];
            }

            if ($userId !== null) {
                $selectedUser = $penyewaList->firstWhere('id', $userId);
                if ($selectedUser) {
                    $rentals = $this->byUser($userId);
                    $detailTitle = "Detail Sewa Penyewa: {$selectedUser->nama}";
                }
            }
        }

        return view('admin.reports.index', compact(
            'tab',
            'year',
            'month',
            'day',
            'tipeGarasi',
            'userId',
            'monthNames',
            'penyewaList',
            'summaryRows',
            'rentals',
            'detailTitle'
        ));
    }

    /**
     * Mengambil data persewaan garasi pada bulan dan tahun tertentu.
     */
    public function byMonth(int $year, int $month): Collection
    {
        if ($month < 1 || $month > 12) {
            return new Collection();
        }

        $start = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $end = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        return Rental::with(['user', 'garage', 'payments'])
            ->whereBetween('tanggal_mulai', [$start, $end])
            ->orderBy('tanggal_mulai')
            ->get();
    }

    /**
     * Mengambil data persewaan garasi pada hari, bulan, dan tahun tertentu.
     */
    public function byDay(int $year, int $month, int $day): Collection
    {
        if (! checkdate($month, $day, $year)) {
            return new Collection();
        }

        $date = Carbon::create($year, $month, $day)->toDateString();

        return Rental::with(['user', 'garage', 'payments'])
            ->whereDate('tanggal_mulai', $date)
            ->latest('id')
            ->get();
    }

    /**
     * Mengambil data persewaan garasi berdasarkan tipe garasi tertentu.
     */
    public function byGarageType(string $tipeGarasi): Collection
    {
        return Rental::with(['user', 'garage', 'payments'])
            ->whereHas('garage', function ($query) use ($tipeGarasi): void {
                $query->where('tipe_garasi', $tipeGarasi);
            })
            ->latest('tanggal_mulai')
            ->get();
    }

    /**
     * Mengambil data persewaan garasi berdasarkan penyewa (user_id) tertentu.
     */
    public function byUser(int $userId): Collection
    {
        return Rental::with(['user', 'garage', 'payments'])
            ->where('user_id', $userId)
            ->latest('tanggal_mulai')
            ->get();
    }
}