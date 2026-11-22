@extends('layouts.app')

@section('title', 'Daftar Sewa Garasi Saya')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Daftar Sewa Garasi Saya</h2>
            <p class="mt-1 text-sm text-slate-500">Anda dapat menyewa lebih dari satu garasi secara bersamaan.</p>
        </div>
        <a href="{{ route('penyewa.rentals.create') }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            + Sewa Garasi Baru
        </a>
    </div>

    @if ($rentals->isEmpty())
        <p class="mt-6 text-sm text-slate-500">Anda belum memiliki transaksi sewa garasi.</p>
    @else
        <div class="mt-5 overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-100 text-slate-700">
                        <th class="px-3 py-2.5 font-semibold">Garasi</th>
                        <th class="px-3 py-2.5 font-semibold">Kendaraan</th>
                        <th class="px-3 py-2.5 font-semibold">Periode Sewa</th>
                        <th class="px-3 py-2.5 font-semibold">Pembayaran</th>
                        <th class="px-3 py-2.5 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($rentals as $rental)
                        @php
                            $isActive = $rental->tanggal_selesai->greaterThanOrEqualTo(now()->startOfDay());
                            $totalPaid = $rental->payments->sum('nominal');
                        @endphp
                        <tr class="align-top hover:bg-slate-50">
                            <td class="px-3 py-2.5">
                                <span class="font-bold text-slate-900">{{ $rental->garage->kode }}</span>
                                <div class="text-xs text-slate-500">{{ $rental->garage->getLabelTipe() }}</div>
                            </td>
                            <td class="px-3 py-2.5">
                                <div class="font-medium text-slate-900">{{ $rental->merek_tipe }}</div>
                                <div class="text-xs text-slate-500">
                                    Tipe: {{ str_replace('_', ' ', strtoupper($rental->tipe_kendaraan)) }}
                                    @if ($rental->plat_nomor)
                                        | Plat: {{ $rental->plat_nomor }}
                                    @endif
                                </div>
                                @if ($rental->kapasitas_baterai !== null)
                                    <div class="text-xs text-slate-500">
                                        Baterai: {{ $rental->kapasitas_baterai }} kWh
                                        @if ($rental->sisa_baterai !== null)
                                            (Sisa awal: {{ $rental->sisa_baterai }} kWh)
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-3 py-2.5">
                                <div>{{ $rental->tanggal_mulai->format('d/m/Y') }} s/d {{ $rental->tanggal_selesai->format('d/m/Y') }}</div>
                                <div class="text-xs text-slate-500">({{ $rental->jumlah_hari }} hari)</div>
                            </td>
                            <td class="px-3 py-2.5">
                                <div class="font-semibold text-slate-900">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
                                @foreach ($rental->payments as $payment)
                                    <div class="mt-0.5 text-xs text-slate-500">
                                        {{ ucfirst(str_replace('_', ' ', $payment->tujuan)) }} ({{ $payment->tipe_sewa }}) -
                                        <a href="{{ asset('storage/' . $payment->bukti_bayar) }}" target="_blank" class="text-blue-600 hover:underline">
                                            Lihat Bukti
                                        </a>
                                    </div>
                                @endforeach
                            </td>
                            <td class="px-3 py-2.5">
                                @if ($isActive)
                                    <span class="inline-block rounded bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">AKTIF</span>
                                @else
                                    <span class="inline-block rounded bg-slate-200 px-2 py-0.5 text-xs font-semibold text-slate-700">SELESAI</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection