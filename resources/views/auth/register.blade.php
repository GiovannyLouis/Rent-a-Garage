@extends('layouts.app')

@section('title', 'Registrasi Penyewa - Sistem Penyewaan Garasi')

@section('content')
    <div class="mx-auto max-w-md">
        <h2 class="text-xl font-bold text-slate-900">Registrasi Penyewa Baru</h2>
        <p class="mt-1 text-sm text-slate-500">Setelah mendaftar, akun Anda akan diverifikasi oleh Admin terlebih dahulu.</p>

        <form action="{{ route('register.submit') }}" method="POST" class="mt-5 space-y-4">
            @csrf

            <div>
                <label for="nik" class="mb-1 block text-sm font-medium text-slate-700">NIK (16 Digit Nomor KTP)</label>
                <input
                    type="text"
                    id="nik"
                    name="nik"
                    value="{{ old('nik') }}"
                    maxlength="16"
                    required
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
            </div>

            <div>
                <label for="nama" class="mb-1 block text-sm font-medium text-slate-700">Nama Lengkap</label>
                <input
                    type="text"
                    id="nama"
                    name="nama"
                    value="{{ old('nama') }}"
                    required
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
            </div>

            <div>
                <label for="no_telp" class="mb-1 block text-sm font-medium text-slate-700">Nomor Telepon</label>
                <input
                    type="text"
                    id="no_telp"
                    name="no_telp"
                    value="{{ old('no_telp') }}"
                    required
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
            </div>

            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email (digunakan sebagai username)</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
            </div>

            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Konfirmasi Password</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
            </div>

            <button type="submit" class="w-full cursor-pointer rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Daftar
            </button>
        </form>

        <p class="mt-5 text-center text-sm text-slate-600">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:underline">Login di sini</a>
        </p>
    </div>
@endsection