@extends('layouts.app')

@section('title', 'Sewa Garasi - Langkah 3')

@section('content')
    <h2 class="text-xl font-bold text-slate-900">Sewa Garasi Baru (Langkah 3 dari 3)</h2>
    <p class="mt-1 text-sm text-slate-500">Periksa ringkasan tanggal sewa dan total biaya, lalu upload bukti pembayaran.</p>

    <div class="mt-5 space-y-3 rounded-md border border-slate-200 bg-slate-50 p-4 text-sm">
        <div class="flex justify-between">
            <span class="text-slate-600">Kendaraan:</span>
            <strong>{{ strtoupper(str_replace('_', ' ', $draft['tipe_kendaraan'])) }} — {{ $draft['merek_tipe'] }} @if($draft['plat_nomor']) ({{ $draft['plat_nomor'] }}) @endif</strong>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-600">Garasi Dipilih:</span>
            <strong>{{ $garage->kode }} ({{ $garage->getLabelTipe() }})</strong>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-600">Tipe Sewa:</span>
            <strong>
                {{ strtoupper($draft['tipe_sewa']) }}
                @if($draft['tipe_sewa'] === \App\Models\Payment::SEWA_HARIAN) ({{ $draft['jumlah_hari'] }} hari) @endif
            </strong>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-600">Tanggal Mulai Sewa:</span>
            <strong>{{ \Carbon\Carbon::parse($tanggalMulai)->format('d/m/Y') }}</strong>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-600">Tanggal Selesai Sewa:</span>
            <strong>{{ \Carbon\Carbon::parse($tanggalSelesai)->format('d/m/Y') }}</strong>
        </div>
        <hr class="border-slate-200">
        <div class="flex justify-between">
            <span class="text-slate-600">Biaya Sewa Garasi:</span>
            <strong>Rp {{ number_format($biayaSewa, 0, ',', '.') }}</strong>
        </div>
        @if ($biayaListrik > 0)
            <div class="flex justify-between">
                <span class="text-slate-600">
                    Biaya Isi Listrik Penuh ({{ $draft['kapasitas_baterai'] - $draft['sisa_baterai'] }} kWh × Rp {{ number_format($garage->harga_kwh, 0, ',', '.') }}):
                </span>
                <strong>Rp {{ number_format($biayaListrik, 0, ',', '.') }}</strong>
            </div>
        @endif
        <div class="flex justify-between border-t border-slate-300 pt-2 text-base font-bold text-blue-900">
            <span>Total Biaya yang Harus Dibayar:</span>
            <span>Rp {{ number_format($totalBiaya, 0, ',', '.') }}</span>
        </div>
    </div>

    <form action="{{ route('penyewa.rentals.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
        @csrf

        <div>
            <label for="bukti_bayar" class="mb-1 block text-sm font-medium text-slate-700">Upload Bukti Pelunasan Biaya (JPG/PNG/WEBP, maks. 2 MB)</label>
            <input type="file" id="bukti_bayar" name="bukti_bayar" accept="image/*" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
        </div>

        <div class="flex items-center gap-3 pt-2">
            <a href="{{ route('penyewa.rentals.step2') }}" class="rounded-md bg-slate-500 px-4 py-2 text-sm font-medium text-white hover:bg-slate-600">
                Kembali
            </a>
            <button type="submit" class="cursor-pointer rounded-md bg-emerald-600 px-5 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                Simpan & Sewa Garasi
            </button>
        </div>
    </form>
@endsection