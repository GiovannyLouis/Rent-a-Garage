@extends('layouts.app')

@section('title', 'Dashboard Penyewa')

@section('content')
    <h2>Dashboard Penyewa</h2>
    <p>Selamat datang, <strong>{{ auth()->user()->nama }}</strong>.</p>
@endsection