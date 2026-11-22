@extends('layouts.app')

@section('title', 'Login - Sistem Penyewaan Garasi')

@section('content')
    <h2>Login</h2>
    <p class="text-muted">Masuk menggunakan email dan password Anda.</p>

    <form action="{{ route('login.submit') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="email">Email (Username)</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit">Login</button>
    </form>

    <p style="margin-top: 16px;">
        Belum punya akun penyewa? <a href="{{ route('register') }}">Daftar di sini</a>
    </p>
@endsection