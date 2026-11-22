@extends('layouts.app')

@section('title', 'Daftar Garasi Sedang Disewa')

@section('content')
    <h2 class="text-xl font-bold text-slate-900">Daftar Garasi Sedang Disewa Hari Ini</h2>
    <p class="mt-1 text-sm text-slate-500">
        Tanggal hari ini: <strong>{{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}</strong>. Pilih garasi untuk memperpanjang tanggal selesai sewa.
    </p>

    @if ($activeRentals->isEmpty())
        <p class="mt-6 text-sm text-slate-500">Tidak ada garasi yang sedang disewa pada hari ini.</p>
    @else
        <div class="mt-5 overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-100 text-slate-700">
                        <th class="px-3 py-2.5 font-semibold">Garasi</th>
                        <th class="px-3 py-2.5 font-semibold">Penyewa</th>
                        <th class="px-3 py-2.5 font-semibold">Kendaraan</th>
                        <th class="px-3 py-2.5 font-semibold">Tanggal Mulai</th>
                        <th class="px-3 py-2.5 font-semibold">Tanggal Selesai</th>
                        <th class="px-3 py-2.5 font-semibold">Total Durasi</th>
                        <th class="px-3 py-2.5 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($activeRentals as $rental)
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 py-2.5">
                                <span class="font-bold text-slate-900">{{ $rental->garage->kode }}</span>
                                <div class="text-xs text-slate-500">{{ $rental->garage->getLabelTipe() }}</div>
                            </td>
                            <td class="px-3 py-2.5">
                                <div class="font-medium text-slate-900">{{ $rental->user->nama }}</div>
                                <div class="text-xs text-slate-500">{{ $rental->user->no_telp }}</div>
                            </td>
                            <td class="px-3 py-2.5">
                                <div>{{ $rental->merek_tipe }}</div>
                                <div class="text-xs text-slate-500">
                                    {{ strtoupper(str_replace('_', ' ', $rental->tipe_kendaraan)) }}
                                    @if ($rental->plat_nomor) | {{ $rental->plat_nomor }} @endif
                                </div>
                            </td>
                            <td class="px-3 py-2.5">{{ $rental->tanggal_mulai->format('d/m/Y') }}</td>
                            <td class="px-3 py-2.5 font-semibold text-slate-900">{{ $rental->tanggal_selesai->format('d/m/Y') }}</td>
                            <td class="px-3 py-2.5">{{ $rental->jumlah_hari }} hari</td>
                            <td class="px-3 py-2.5">
                                <a href="{{ route('admin.rentals.extend.form', $rental->id) }}" class="inline-block rounded bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                    Perpanjang
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection