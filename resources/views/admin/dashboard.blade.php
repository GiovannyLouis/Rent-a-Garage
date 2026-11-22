@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <h2 class="text-xl font-bold text-slate-900">Dashboard Admin</h2>
    <p class="mt-1 text-sm text-slate-600">
        Selamat datang, <strong class="text-slate-900">{{ auth()->user()->nama }}</strong>.
    </p>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
            <p class="text-sm font-medium text-slate-500">Pendaftar Menunggu Verifikasi</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $pendingCount ?? 0 }} orang</p>
            <a href="{{ route('admin.verifications.index') }}" class="mt-3 inline-block rounded bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700">
                Kelola Verifikasi Penyewa
            </a>
        </div>

        <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
            <p class="text-sm font-medium text-slate-500">Total Unit Garasi</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">
                {{ $totalGarages ?? 0 }} unit
                <span class="text-sm font-normal text-slate-500">(Tersedia: {{ $availableGarages ?? 0 }})</span>
            </p>
            <a href="{{ route('admin.garages.index') }}" class="mt-3 inline-block rounded bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700">
                Kelola Garasi
            </a>
        </div>

        <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
            <p class="text-sm font-medium text-slate-500">Garasi Sedang Disewa Hari Ini</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $activeRentalsCount ?? 0 }} garasi</p>
            <a href="{{ route('admin.rentals.index') }}" class="mt-3 inline-block rounded bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700">
                Perpanjang Status Garasi
            </a>
        </div>
    </div>
@endsection