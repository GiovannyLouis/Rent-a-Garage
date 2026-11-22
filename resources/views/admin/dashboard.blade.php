@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <h2>Dashboard Admin</h2>
    <p>Selamat datang, <strong>{{ auth()->user()->nama }}</strong>.</p>
@endsection