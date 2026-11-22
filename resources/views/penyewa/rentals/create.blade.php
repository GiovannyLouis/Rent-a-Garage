@extends('layouts.app')

@section('title', 'Sewa Garasi - Langkah 1')

@section('content')
    <h2 class="text-xl font-bold text-slate-900">Sewa Garasi Baru (Langkah 1 dari 3)</h2>
    <p class="mt-1 text-sm text-slate-500">Masukkan detail kendaraan yang akan menyewa garasi.</p>

    <form action="{{ route('penyewa.rentals.step1') }}" method="POST" class="mt-5 space-y-4">
        @csrf

        <div>
            <label for="tipe_kendaraan" class="mb-1 block text-sm font-medium text-slate-700">Tipe Kendaraan</label>
            @php $selectedKendaraan = old('tipe_kendaraan', $draft['tipe_kendaraan'] ?? ''); @endphp
            <select name="tipe_kendaraan" id="tipe_kendaraan" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">-- Pilih Tipe Kendaraan --</option>
                <option value="{{ \App\Models\Rental::KENDARAAN_SEPEDA }}" @selected($selectedKendaraan === \App\Models\Rental::KENDARAAN_SEPEDA)>Sepeda</option>
                <option value="{{ \App\Models\Rental::KENDARAAN_SEPEDA_MOTOR }}" @selected($selectedKendaraan === \App\Models\Rental::KENDARAAN_SEPEDA_MOTOR)>Sepeda Motor</option>
                <option value="{{ \App\Models\Rental::KENDARAAN_MOBIL }}" @selected($selectedKendaraan === \App\Models\Rental::KENDARAAN_MOBIL)>Mobil</option>
                <option value="{{ \App\Models\Rental::KENDARAAN_MOBIL_LISTRIK }}" @selected($selectedKendaraan === \App\Models\Rental::KENDARAAN_MOBIL_LISTRIK)>Mobil Listrik</option>
            </select>
        </div>

        <div>
            <label for="merek_tipe" class="mb-1 block text-sm font-medium text-slate-700">Merek & Tipe Kendaraan</label>
            <input
                type="text"
                id="merek_tipe"
                name="merek_tipe"
                value="{{ old('merek_tipe', $draft['merek_tipe'] ?? '') }}"
                placeholder="Contoh: Honda Vario 160 / Hyundai Ioniq 5"
                required
                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
            >
        </div>

        <div id="plat_group" class="hidden">
            <label for="plat_nomor" class="mb-1 block text-sm font-medium text-slate-700">Nomor Plat Kendaraan</label>
            <input
                type="text"
                id="plat_nomor"
                name="plat_nomor"
                value="{{ old('plat_nomor', $draft['plat_nomor'] ?? '') }}"
                placeholder="Contoh: B 1234 ABC"
                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
            >
        </div>

        <div id="kapasitas_group" class="hidden">
            <label for="kapasitas_baterai" class="mb-1 block text-sm font-medium text-slate-700">Kapasitas Listrik Baterai (kWh)</label>
            <input
                type="number"
                step="0.01"
                min="0.1"
                id="kapasitas_baterai"
                name="kapasitas_baterai"
                value="{{ old('kapasitas_baterai', $draft['kapasitas_baterai'] ?? '') }}"
                placeholder="Contoh: 72.6"
                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
            >
        </div>

        <div class="flex items-center gap-3 pt-2">
            <a href="{{ route('penyewa.rentals.index') }}" class="rounded-md bg-slate-500 px-4 py-2 text-sm font-medium text-white hover:bg-slate-600">
                Batal
            </a>
            <button type="submit" class="cursor-pointer rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Lanjut ke Langkah 2
            </button>
        </div>
    </form>

    <script>
        const tipeKendaraan = document.getElementById('tipe_kendaraan');
        const platGroup = document.getElementById('plat_group');
        const platInput = document.getElementById('plat_nomor');
        const kapasitasGroup = document.getElementById('kapasitas_group');
        const kapasitasInput = document.getElementById('kapasitas_baterai');

        function toggleVehicleInputs() {
            const val = tipeKendaraan.value;
            const showPlat = val !== '' && val !== '{{ \App\Models\Rental::KENDARAAN_SEPEDA }}';
            const showKapasitas = val === '{{ \App\Models\Rental::KENDARAAN_MOBIL_LISTRIK }}';

            platGroup.classList.toggle('hidden', !showPlat);
            platInput.required = showPlat;
            if (!showPlat) platInput.value = '';

            kapasitasGroup.classList.toggle('hidden', !showKapasitas);
            kapasitasInput.required = showKapasitas;
            if (!showKapasitas) kapasitasInput.value = '';
        }

        tipeKendaraan.addEventListener('change', toggleVehicleInputs);
        toggleVehicleInputs();
    </script>
@endsection