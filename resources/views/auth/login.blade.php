@extends('layouts.app')

@section('title', 'Login - Sistem Penyewaan Garasi')

@section('content')
    <div class="mx-auto max-w-md">
        <h2 class="text-xl font-bold text-slate-900">Login</h2>
        <p class="mt-1 text-sm text-slate-500">Masuk menggunakan email dan password Anda.</p>

        <form action="{{ route('login.submit') }}" method="POST" class="mt-5 space-y-4">
            @csrf

            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email (Username)</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
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

            <button type="submit" class="w-full cursor-pointer rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Login
            </button>
        </form>

        <p class="mt-5 text-center text-sm text-slate-600">
            Belum punya akun penyewa?
            <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:underline">Daftar di sini</a>
        </p>
    </div>
@endsection