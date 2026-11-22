@extends('layouts.app')

@section('title', 'Laporan Sewa Garasi')

@section('content')
    <h2 class="text-xl font-bold text-slate-900">Laporan Persewaan Garasi</h2>
    <p class="mt-1 text-sm text-slate-500">Lihat rekapitulasi dan detail transaksi sewa garasi berdasarkan kategori laporan.</p>

    {{-- Navigasi 4 Kategori Laporan --}}
    <div class="mt-5 flex flex-wrap gap-2 border-b border-slate-200 pb-3 text-sm">
        <a
            href="{{ route('admin.reports.index', ['tab' => 'month', 'year' => $year]) }}"
            @class([
                'rounded-md px-3 py-1.5 font-medium',
                'bg-blue-600 text-white' => $tab === 'month',
                'bg-slate-100 text-slate-700 hover:bg-slate-200' => $tab !== 'month',
            ])
        >
            Per Bulan (Tahun)
        </a>
        <a
            href="{{ route('admin.reports.index', ['tab' => 'day', 'year' => $year, 'month' => $month ?? now()->month]) }}"
            @class([
                'rounded-md px-3 py-1.5 font-medium',
                'bg-blue-600 text-white' => $tab === 'day',
                'bg-slate-100 text-slate-700 hover:bg-slate-200' => $tab !== 'day',
            ])
        >
            Per Hari (Bulan)
        </a>
        <a
            href="{{ route('admin.reports.index', ['tab' => 'garage_type']) }}"
            @class([
                'rounded-md px-3 py-1.5 font-medium',
                'bg-blue-600 text-white' => $tab === 'garage_type',
                'bg-slate-100 text-slate-700 hover:bg-slate-200' => $tab !== 'garage_type',
            ])
        >
            Per Tipe Garasi
        </a>
        <a
            href="{{ route('admin.reports.index', ['tab' => 'user']) }}"
            @class([
                'rounded-md px-3 py-1.5 font-medium',
                'bg-blue-600 text-white' => $tab === 'user',
                'bg-slate-100 text-slate-700 hover:bg-slate-200' => $tab !== 'user',
            ])
        >
            Per Penyewa
        </a>
    </div>

    {{-- Filter & Rekap Sesuai Tab Aktif --}}
    @if ($tab === 'month')
        <h3 class="mt-5 text-base font-semibold text-slate-800">Rekap Per Bulan Tahun {{ $year }}</h3>
        <div class="mt-2 overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-100 text-slate-700">
                        <th class="px-3 py-2 font-semibold">Bulan</th>
                        <th class="px-3 py-2 font-semibold">Jumlah Sewa</th>
                        <th class="px-3 py-2 font-semibold">Total Pendapatan</th>
                        <th class="px-3 py-2 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($summaryRows as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 py-2 font-medium text-slate-900">{{ $row['label'] }}</td>
                            <td class="px-3 py-2">{{ $row['count'] }} transaksi</td>
                            <td class="px-3 py-2 font-semibold">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2">
                                <a href="{{ route('admin.reports.index', ['tab' => 'month', 'year' => $year, 'month' => $row['month']]) }}" class="text-blue-600 hover:underline">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-300 bg-slate-50 font-bold text-slate-900">
                        <td class="px-3 py-2">Total Tahun {{ $year }}</td>
                        <td class="px-3 py-2">{{ collect($summaryRows)->sum('count') }} transaksi</td>
                        <td class="px-3 py-2" colspan="2">Rp {{ number_format(collect($summaryRows)->sum('total'), 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <form action="{{ route('admin.reports.index') }}" method="GET" class="mt-4 flex flex-wrap items-end gap-3 rounded-md border border-slate-200 bg-slate-50 p-4 text-sm">
            <input type="hidden" name="tab" value="month">
            <div>
                <label for="year" class="mb-1 block font-medium text-slate-700">Tahun</label>
                <input type="number" id="year" name="year" value="{{ $year }}" min="2020" max="2100" class="w-28 rounded-md border border-slate-300 bg-white px-3 py-1.5">
            </div>
            <div>
                <label for="month" class="mb-1 block font-medium text-slate-700">Detail Bulan (Opsional)</label>
                <select name="month" id="month" class="rounded-md border border-slate-300 bg-white px-3 py-1.5">
                    <option value="">-- Semua Bulan (Rekap Saja) --</option>
                    @foreach ($monthNames as $m => $label)
                        <option value="{{ $m }}" @selected($month === $m)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="cursor-pointer rounded-md bg-blue-600 px-4 py-1.5 font-medium text-white hover:bg-blue-700">Tampilkan</button>
        </form>

    @elseif ($tab === 'day')
        <h3 class="mt-5 text-base font-semibold text-slate-800">Rekap Harian — {{ $monthNames[$month] }} {{ $year }}</h3>
        @if (empty($summaryRows))
            <p class="mt-2 text-sm text-slate-500">Belum ada transaksi sewa pada bulan {{ $monthNames[$month] }} {{ $year }}.</p>
        @else
            <div class="mt-2 overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-100 text-slate-700">
                            <th class="px-3 py-2 font-semibold">Tanggal</th>
                            <th class="px-3 py-2 font-semibold">Jumlah Sewa</th>
                            <th class="px-3 py-2 font-semibold">Total Pendapatan</th>
                            <th class="px-3 py-2 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($summaryRows as $row)
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2 font-medium text-slate-900">{{ $row['date'] }}</td>
                                <td class="px-3 py-2">{{ $row['count'] }} transaksi</td>
                                <td class="px-3 py-2 font-semibold">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2">
                                    <a href="{{ route('admin.reports.index', ['tab' => 'day', 'year' => $year, 'month' => $month, 'day' => $row['day']]) }}" class="text-blue-600 hover:underline">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <form action="{{ route('admin.reports.index') }}" method="GET" class="mt-4 flex flex-wrap items-end gap-3 rounded-md border border-slate-200 bg-slate-50 p-4 text-sm">
            <input type="hidden" name="tab" value="day">
            <div>
                <label for="year" class="mb-1 block font-medium text-slate-700">Tahun</label>
                <input type="number" id="year" name="year" value="{{ $year }}" min="2020" max="2100" class="w-28 rounded-md border border-slate-300 bg-white px-3 py-1.5">
            </div>
            <div>
                <label for="month" class="mb-1 block font-medium text-slate-700">Bulan</label>
                <select name="month" id="month" class="rounded-md border border-slate-300 bg-white px-3 py-1.5">
                    @foreach ($monthNames as $m => $label)
                        <option value="{{ $m }}" @selected($month === $m)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="day" class="mb-1 block font-medium text-slate-700">Tanggal (Opsional)</label>
                <input type="number" id="day" name="day" value="{{ $day }}" min="1" max="31" placeholder="1-31" class="w-24 rounded-md border border-slate-300 bg-white px-3 py-1.5">
            </div>
            <button type="submit" class="cursor-pointer rounded-md bg-blue-600 px-4 py-1.5 font-medium text-white hover:bg-blue-700">Tampilkan</button>
        </form>

    @elseif ($tab === 'garage_type')
        <h3 class="mt-5 text-base font-semibold text-slate-800">Rekap Per Tipe Garasi</h3>
        <div class="mt-2 overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-100 text-slate-700">
                        <th class="px-3 py-2 font-semibold">Tipe Garasi</th>
                        <th class="px-3 py-2 font-semibold">Jumlah Sewa</th>
                        <th class="px-3 py-2 font-semibold">Total Pendapatan</th>
                        <th class="px-3 py-2 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($summaryRows as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 py-2 font-medium text-slate-900">{{ $row['label'] }}</td>
                            <td class="px-3 py-2">{{ $row['count'] }} transaksi</td>
                            <td class="px-3 py-2 font-semibold">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2">
                                <a href="{{ route('admin.reports.index', ['tab' => 'garage_type', 'tipe_garasi' => $row['tipe_garasi']]) }}" class="text-blue-600 hover:underline">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <form action="{{ route('admin.reports.index') }}" method="GET" class="mt-4 flex flex-wrap items-end gap-3 rounded-md border border-slate-200 bg-slate-50 p-4 text-sm">
            <input type="hidden" name="tab" value="garage_type">
            <div>
                <label for="tipe_garasi" class="mb-1 block font-medium text-slate-700">Pilih Tipe Garasi</label>
                <select name="tipe_garasi" id="tipe_garasi" class="rounded-md border border-slate-300 bg-white px-3 py-1.5">
                    <option value="">-- Semua Tipe (Rekap Saja) --</option>
                    <option value="{{ \App\Models\Garage::TIPE_SEPEDA }}" @selected($tipeGarasi === \App\Models\Garage::TIPE_SEPEDA)>Garasi Sepeda (S)</option>
                    <option value="{{ \App\Models\Garage::TIPE_MOBIL }}" @selected($tipeGarasi === \App\Models\Garage::TIPE_MOBIL)>Garasi Mobil (C)</option>
                    <option value="{{ \App\Models\Garage::TIPE_MOBIL_LISTRIK }}" @selected($tipeGarasi === \App\Models\Garage::TIPE_MOBIL_LISTRIK)>Garasi Mobil Listrik (E)</option>
                </select>
            </div>
            <button type="submit" class="cursor-pointer rounded-md bg-blue-600 px-4 py-1.5 font-medium text-white hover:bg-blue-700">Tampilkan</button>
        </form>

    @elseif ($tab === 'user')
        <h3 class="mt-5 text-base font-semibold text-slate-800">Rekap Per Penyewa</h3>
        @if (empty($summaryRows))
            <p class="mt-2 text-sm text-slate-500">Belum ada data penyewa terdaftar.</p>
        @else
            <div class="mt-2 overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-100 text-slate-700">
                            <th class="px-3 py-2 font-semibold">Nama Penyewa</th>
                            <th class="px-3 py-2 font-semibold">NIK</th>
                            <th class="px-3 py-2 font-semibold">Jumlah Sewa</th>
                            <th class="px-3 py-2 font-semibold">Total Pembayaran</th>
                            <th class="px-3 py-2 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($summaryRows as $row)
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2 font-medium text-slate-900">{{ $row['nama'] }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $row['nik'] }}</td>
                                <td class="px-3 py-2">{{ $row['count'] }} transaksi</td>
                                <td class="px-3 py-2 font-semibold">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2">
                                    <a href="{{ route('admin.reports.index', ['tab' => 'user', 'user_id' => $row['user_id']]) }}" class="text-blue-600 hover:underline">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <form action="{{ route('admin.reports.index') }}" method="GET" class="mt-4 flex flex-wrap items-end gap-3 rounded-md border border-slate-200 bg-slate-50 p-4 text-sm">
            <input type="hidden" name="tab" value="user">
            <div>
                <label for="user_id" class="mb-1 block font-medium text-slate-700">Pilih Penyewa</label>
                <select name="user_id" id="user_id" class="rounded-md border border-slate-300 bg-white px-3 py-1.5">
                    <option value="">-- Semua Penyewa (Rekap Saja) --</option>
                    @foreach ($penyewaList as $p)
                        <option value="{{ $p->id }}" @selected($userId === $p->id)>{{ $p->nama }} ({{ $p->nik }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="cursor-pointer rounded-md bg-blue-600 px-4 py-1.5 font-medium text-white hover:bg-blue-700">Tampilkan</button>
        </form>
    @endif

    {{-- Tabel Detail Transaksi (digunakan bersama oleh ke-4 kategori saat filter detail dipilih) --}}
    @if ($rentals !== null)
        <h3 class="mt-8 border-t border-slate-200 pt-5 text-base font-semibold text-slate-900">{{ $detailTitle }}</h3>

        @if ($rentals->isEmpty())
            <p class="mt-2 text-sm text-slate-500">Tidak ada data transaksi persewaan untuk filter ini.</p>
        @else
            <div class="mt-3 overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-100 text-slate-700">
                            <th class="px-3 py-2 font-semibold">Garasi</th>
                            <th class="px-3 py-2 font-semibold">Penyewa</th>
                            <th class="px-3 py-2 font-semibold">Kendaraan</th>
                            <th class="px-3 py-2 font-semibold">Periode Sewa</th>
                            <th class="px-3 py-2 font-semibold">Rincian Pembayaran</th>
                            <th class="px-3 py-2 font-semibold">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @php $grandTotal = 0; @endphp
                        @foreach ($rentals as $rental)
                            @php
                                $rentalTotal = $rental->payments->sum('nominal');
                                $grandTotal += $rentalTotal;
                            @endphp
                            <tr class="align-top hover:bg-slate-50">
                                <td class="px-3 py-2">
                                    <strong>{{ $rental->garage->kode }}</strong>
                                    <div class="text-xs text-slate-500">{{ $rental->garage->getLabelTipe() }}</div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="font-medium text-slate-900">{{ $rental->user->nama }}</div>
                                    <div class="text-xs text-slate-500">{{ $rental->user->no_telp }}</div>
                                </td>
                                <td class="px-3 py-2">
                                    <div>{{ $rental->merek_tipe }}</div>
                                    <div class="text-xs text-slate-500">
                                        {{ strtoupper(str_replace('_', ' ', $rental->tipe_kendaraan)) }}
                                        @if ($rental->plat_nomor) | {{ $rental->plat_nomor }} @endif
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <div>{{ $rental->tanggal_mulai->format('d/m/Y') }} s/d {{ $rental->tanggal_selesai->format('d/m/Y') }}</div>
                                    <div class="text-xs text-slate-500">({{ $rental->jumlah_hari }} hari)</div>
                                </td>
                                <td class="px-3 py-2">
                                    @foreach ($rental->payments as $payment)
                                        <div class="text-xs text-slate-600">
                                            {{ ucfirst(str_replace('_', ' ', $payment->tujuan)) }}:
                                            Rp {{ number_format($payment->nominal, 0, ',', '.') }}
                                            (<a href="{{ asset('storage/' . $payment->bukti_bayar) }}" target="_blank" class="text-blue-600 hover:underline">bukti</a>)
                                        </div>
                                    @endforeach
                                </td>
                                <td class="px-3 py-2 font-bold text-slate-900">
                                    Rp {{ number_format($rentalTotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-300 bg-slate-50 font-bold text-slate-900">
                            <td colspan="5" class="px-3 py-2 text-right">Grand Total ({{ $rentals->count() }} sewa):</td>
                            <td class="px-3 py-2">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    @endif
@endsection