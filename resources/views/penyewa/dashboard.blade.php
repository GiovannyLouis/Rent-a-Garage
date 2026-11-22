@extends('layouts.app')

@section('title', 'Dashboard Penyewa')

@section('content')
    <h2 class="text-xl font-bold text-slate-900">Dashboard Penyewa</h2>
    <p class="mt-1 text-sm text-slate-600">
        Selamat datang, <strong class="text-slate-900">{{ auth()->user()->nama }}</strong>.
    </p>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
            <p class="text-sm font-medium text-slate-500">Sewa Aktif Saat Ini</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $activeRentalsCount ?? 0 }} garasi</p>
            <a href="{{ route('penyewa.rentals.index') }}" class="mt-3 inline-block rounded bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-900">
                Lihat Daftar Sewa Saya
            </a>
        </div>

        <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
            <p class="text-sm font-medium text-slate-500">Total Riwayat Transaksi Sewa</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $totalRentalsCount ?? 0 }} transaksi</p>
            <a href="{{ route('penyewa.rentals.create') }}" class="mt-3 inline-block rounded bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700">
                + Sewa Garasi Baru
            </a>
        </div>
    </div>
@endsection