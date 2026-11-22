@extends('layouts.app')

@section('title', 'Dashboard Penyewa')

@section('content')
    <h2 class="text-xl font-bold text-slate-900">Dashboard Penyewa</h2>
    <p class="mt-1 text-sm text-slate-600">
        Selamat datang, <strong class="text-slate-900">{{ auth()->user()->nama }}</strong>.
    </p>
@endsection