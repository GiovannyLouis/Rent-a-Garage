@extends('layouts.app')

@section('title', 'Perpanjang Sewa - Langkah 2')

@section('content')
    <h2 class="text-xl font-bold text-slate-900">Perpanjang Garasi {{ $rental->garage->kode }} (Langkah 2 dari 2)</h2>
    <p class="mt-1 text-sm text-slate-500">Periksa rincian tanggal selesai baru dan sisa biaya perpanjangan, lalu upload bukti pembayaran.</p>

    <div class="mt-5 space-y-3 rounded-md border border-blue-200 bg-blue-50 p-4 text-sm">
        <div class="flex justify-between">
            <span class="text-slate-600">Penyewa:</span>
            <strong>{{ $rental->user->nama }}</strong>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-600">Garasi:</span>
            <strong>{{ $rental->garage->kode }} ({{ $rental->garage->getLabelTipe() }})</strong>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-600">Tanggal Selesai Lama:</span>
            <strong>{{ $rental->tanggal_selesai->format('d/m/Y') }}</strong>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-600">Tanggal Selesai Baru:</span>
            <strong class="text-blue-800">{{ \Carbon\Carbon::parse($newTanggalSelesai)->format('d/m/Y') }}</strong>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-600">Tambahan Durasi:</span>
            <strong>+{{ $tambahanHari }} hari ({{ strtoupper($tipeSewa) }})</strong>
        </div>
        <div class="flex justify-between border-t border-blue-200 pt-2 text-base font-bold text-blue-900">
            <span>Sisa Biaya yang Harus Dibayarkan:</span>
            <span>Rp {{ number_format($sisaBiaya, 0, ',', '.') }}</span>
        </div>
    </div>

    <form action="{{ route('admin.rentals.extend', $rental->id) }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
        @csrf
        <input type="hidden" name="tipe_sewa" value="{{ $tipeSewa }}">
        @if ($tipeSewa === \App\Models\Payment::SEWA_HARIAN)
            <input type="hidden" name="jumlah_hari" value="{{ $jumlahHariInput }}">
        @endif

        <div>
            <label for="bukti_bayar" class="mb-1 block text-sm font-medium text-slate-700">Upload Bukti Pembayaran Perpanjangan (JPG/PNG/WEBP, maks. 2 MB)</label>
            <input type="file" id="bukti_bayar" name="bukti_bayar" accept="image/*" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
        </div>

        <div class="flex items-center gap-3 pt-2">
            <a href="{{ route('admin.rentals.extend.form', array_filter([
                'rentalId' => $rental->id,
                'tipe_sewa' => $tipeSewa,
                'jumlah_hari' => $tipeSewa === \App\Models\Payment::SEWA_HARIAN ? $jumlahHariInput : null,
            ])) }}" class="rounded-md bg-slate-500 px-4 py-2 text-sm font-medium text-white hover:bg-slate-600">
                &laquo; Kembali
            </a>
            <button type="submit" class="cursor-pointer rounded-md bg-emerald-600 px-5 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                Simpan Perpanjangan
            </button>
        </div>
    </form>
@endsection