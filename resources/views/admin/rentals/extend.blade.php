@extends('layouts.app')

@section('title', 'Perpanjang Sewa - Langkah 1')

@section('content')
    <h2 class="text-xl font-bold text-slate-900">Perpanjang Garasi {{ $rental->garage->kode }} (Langkah 1 dari 2)</h2>
    <p class="mt-1 text-sm text-slate-500">Pilih tipe perpanjangan (Harian / Bulanan) dan jumlah hari tambahan.</p>

    {{-- Info Sewa Saat Ini --}}
    <div class="mt-4 grid grid-cols-1 gap-2 rounded-md border border-slate-200 bg-slate-50 p-4 text-sm sm:grid-cols-2">
        <div><span class="text-slate-500">Penyewa:</span> <strong>{{ $rental->user->nama }}</strong></div>
        <div><span class="text-slate-500">Garasi:</span> <strong>{{ $rental->garage->kode }} ({{ $rental->garage->getLabelTipe() }})</strong></div>
        <div><span class="text-slate-500">Kendaraan:</span> <strong>{{ $rental->merek_tipe }} @if($rental->plat_nomor) ({{ $rental->plat_nomor }}) @endif</strong></div>
        <div><span class="text-slate-500">Tarif:</span> <strong>Rp {{ number_format($rental->garage->harga_harian, 0, ',', '.') }}/hari | Rp {{ number_format($rental->garage->getHargaBulanan(), 0, ',', '.') }}/bln</strong></div>
        <div><span class="text-slate-500">Tanggal Mulai:</span> <strong>{{ $rental->tanggal_mulai->format('d/m/Y') }}</strong></div>
        <div><span class="text-slate-500">Tanggal Selesai Saat Ini:</span> <strong class="text-blue-700">{{ $rental->tanggal_selesai->format('d/m/Y') }} ({{ $rental->jumlah_hari }} hari)</strong></div>
    </div>

    <form action="{{ route('admin.rentals.extend.confirm', $rental->id) }}" method="GET" class="mt-5 space-y-4">
        @php $selectedSewa = old('tipe_sewa', request('tipe_sewa', \App\Models\Payment::SEWA_HARIAN)); @endphp

        <div>
            <label for="tipe_sewa" class="mb-1 block text-sm font-medium text-slate-700">Tipe Perpanjangan</label>
            <select name="tipe_sewa" id="tipe_sewa" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="{{ \App\Models\Payment::SEWA_HARIAN }}" @selected($selectedSewa === \App\Models\Payment::SEWA_HARIAN)>Harian</option>
                <option value="{{ \App\Models\Payment::SEWA_BULANAN }}" @selected($selectedSewa === \App\Models\Payment::SEWA_BULANAN)>Bulanan (25× harga harian)</option>
            </select>
        </div>

        <div id="jumlah_hari_group">
            <label for="jumlah_hari" class="mb-1 block text-sm font-medium text-slate-700">
                Jumlah Hari Tambahan (Maks. {{ $maxDailyDays }} hari)
            </label>
            <input
                type="number"
                id="jumlah_hari"
                name="jumlah_hari"
                value="{{ old('jumlah_hari', request('jumlah_hari', 1)) }}"
                min="1"
                max="{{ $maxDailyDays }}"
                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
            >
        </div>

        <div class="flex items-center gap-3 pt-2">
            <a href="{{ route('admin.rentals.index') }}" class="rounded-md bg-slate-500 px-4 py-2 text-sm font-medium text-white hover:bg-slate-600">
                &laquo; Kembali
            </a>
            <button type="submit" class="cursor-pointer rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Lanjut ke Rincian Biaya &raquo;
            </button>
        </div>
    </form>

    <script>
        const tipeSewaSelect = document.getElementById('tipe_sewa');
        const jumlahHariGroup = document.getElementById('jumlah_hari_group');
        const jumlahHariInput = document.getElementById('jumlah_hari');

        function toggleDaysInput() {
            const isDaily = tipeSewaSelect.value === '{{ \App\Models\Payment::SEWA_HARIAN }}';

            jumlahHariGroup.classList.toggle('hidden', !isDaily);
            jumlahHariInput.required = isDaily;

            if (isDaily && (!jumlahHariInput.value || jumlahHariInput.value === '0')) {
                jumlahHariInput.value = '1';
            } else if (!isDaily) {
                jumlahHariInput.value = '';
            }
        }

        tipeSewaSelect.addEventListener('change', toggleDaysInput);
        toggleDaysInput();
    </script>
@endsection