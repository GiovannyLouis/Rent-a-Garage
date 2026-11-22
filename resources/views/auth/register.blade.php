@extends('layouts.app')

@section('title', 'Registrasi Penyewa - Sistem Penyewaan Garasi')

@section('content')
    <h2>Registrasi Penyewa Baru</h2>
    <p class="text-muted">Setelah mendaftar, akun Anda akan diverifikasi oleh Admin terlebih dahulu.</p>

    <form action="{{ route('register.submit') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="nik">NIK (16 Digit Nomor KTP)</label>
            <input type="text" id="nik" name="nik" value="{{ old('nik') }}" maxlength="16" required>
        </div>

        <div class="form-group">
            <label for="nama">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" value="{{ old('nama') }}" required>
        </div>

        <div class="form-group">
            <label for="no_telp">Nomor Telepon</label>
            <input type="text" id="no_telp" name="no_telp" value="{{ old('no_telp') }}" required>
        </div>

        <div class="form-group">
            <label for="email">Email (digunakan sebagai username)</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Konfirmasi Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>
        </div>

        <button type="submit">Daftar</button>
    </form>

    <p style="margin-top: 16px;">
        Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a>
    </p>
@endsection