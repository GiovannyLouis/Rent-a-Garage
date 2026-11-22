@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <h2>Dashboard Admin</h2>
    <p>Selamat datang, <strong>{{ auth()->user()->nama }}</strong>.</p>

    <div style="padding: 14px; border: 1px solid #d1d5db; border-radius: 6px; margin-top: 16px;">
        <strong>Pendaftar Menunggu Verifikasi:</strong> {{ $pendingCount ?? 0 }} orang
        <div style="margin-top: 10px;">
            <a href="{{ route('admin.verifications.index') }}" class="btn">Kelola Verifikasi Penyewa</a>
        </div>
    </div>
@endsection