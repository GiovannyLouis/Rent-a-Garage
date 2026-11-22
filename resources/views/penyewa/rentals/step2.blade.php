@extends('layouts.app')

@section('title', 'Sewa Garasi - Langkah 2')

@section('content')
    <h2 class="text-xl font-bold text-slate-900">Sewa Garasi Baru (Langkah 2 dari 3)</h2>
    <p class="mt-1 text-sm text-slate-500">Pilih garasi yang tersedia pada hari ini ({{ $tanggalMulai }}) dan tentukan periode sewa.</p>

    <div class="mt-4 rounded-md border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700">
        <strong>Kendaraan:</strong> {{ strtoupper(str_replace('_', ' ', $draft['tipe_kendaraan'])) }} — {{ $draft['merek_tipe'] }}
        @if ($draft['plat_nomor']) | Plat: {{ $draft['plat_nomor'] }} @endif
        @if ($draft['kapasitas_baterai']) | Baterai: {{ $draft['kapasitas_baterai'] }} kWh @endif
    </div>

    @if ($availableGarages->isEmpty())
        <div class="mt-4 rounded-md border border-amber-300 bg-amber-50 p-4 text-sm text-amber-800">
            Maaf, saat ini tidak ada garasi yang tersedia untuk tipe kendaraan Anda.
        </div>
        <div class="mt-4">
            <a href="{{ route('penyewa.rentals.create') }}" class="rounded-md bg-slate-600 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                &laquo; Kembali
            </a>
        </div>
    @else
        <form action="{{ route('penyewa.rentals.step2.store') }}" method="POST" class="mt-5 space-y-4">
            @csrf

            <div>
                <label for="garage_id" class="mb-1 block text-sm font-medium text-slate-700">Pilih Garasi Tersedia</label>
                @php $selectedGarage = old('garage_id', $draft['garage_id'] ?? ''); @endphp
                <select name="garage_id" id="garage_id" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
                    <option value="">-- Pilih Garasi --</option>
                    @foreach ($availableGarages as $garage)
                        <option
                            value="{{ $garage->id }}"
                            data-tipe="{{ $garage->tipe_garasi }}"
                            @selected((string) $selectedGarage === (string) $garage->id)
                        >
                            {{ $garage->kode }} - {{ $garage->getLabelTipe() }}
                            (Harian: Rp {{ number_format($garage->harga_harian, 0, ',', '.') }}
                            | Bulanan: Rp {{ number_format($garage->getHargaBulanan(), 0, ',', '.') }}
                            @if ($garage->harga_kwh !== null)
                                | Listrik: Rp {{ number_format($garage->harga_kwh, 0, ',', '.') }}/kWh
                            @endif)
                        </option>
                    @endforeach
                </select>
            </div>

            @if ($draft['tipe_kendaraan'] === \App\Models\Rental::KENDARAAN_MOBIL_LISTRIK)
                <div id="sisa_baterai_group" class="hidden">
                    <label for="sisa_baterai" class="mb-1 block text-sm font-medium text-slate-700">
                        Sisa Baterai Saat Ini (kWh)
                        <span class="text-xs font-normal text-slate-500">(Pengisian hingga penuh {{ $draft['kapasitas_baterai'] }} kWh)</span>
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        max="{{ $draft['kapasitas_baterai'] }}"
                        id="sisa_baterai"
                        name="sisa_baterai"
                        value="{{ old('sisa_baterai', $draft['sisa_baterai'] ?? '') }}"
                        placeholder="Contoh: 20"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    >
                </div>
            @endif

            <div>
                <label for="tipe_sewa" class="mb-1 block text-sm font-medium text-slate-700">Tipe Sewa</label>
                @php $selectedSewa = old('tipe_sewa', $draft['tipe_sewa'] ?? \App\Models\Payment::SEWA_HARIAN); @endphp
                <select name="tipe_sewa" id="tipe_sewa" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
                    <option value="{{ \App\Models\Payment::SEWA_HARIAN }}" @selected($selectedSewa === \App\Models\Payment::SEWA_HARIAN)>Harian</option>
                    <option value="{{ \App\Models\Payment::SEWA_BULANAN }}" @selected($selectedSewa === \App\Models\Payment::SEWA_BULANAN)>Bulanan (25x harga harian)</option>
                </select>
            </div>

            <div id="jumlah_hari_group">
                <label for="jumlah_hari" class="mb-1 block text-sm font-medium text-slate-700">
                    Jumlah Hari (Maks. {{ $maxDailyDays }} hari)
                </label>
                <input
                    type="number"
                    id="jumlah_hari"
                    name="jumlah_hari"
                    value="{{ old('jumlah_hari', ($draft['jumlah_hari'] ?? 0) > 0 ? $draft['jumlah_hari'] : 1) }}"
                    min="1"
                    max="{{ $maxDailyDays }}"
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                >
            </div>

            <div class="flex items-center gap-3 pt-2">
                <a href="{{ route('penyewa.rentals.create') }}" class="rounded-md bg-slate-500 px-4 py-2 text-sm font-medium text-white hover:bg-slate-600">
                    Kembali
                </a>
                <button type="submit" class="cursor-pointer rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Lanjut ke Konfirmasi
                </button>
            </div>
        </form>

        <script>
            const garageSelect = document.getElementById('garage_id');
            const sisaGroup = document.getElementById('sisa_baterai_group');
            const sisaInput = document.getElementById('sisa_baterai');

            const tipeSewaSelect = document.getElementById('tipe_sewa');
            const jumlahHariGroup = document.getElementById('jumlah_hari_group');
            const jumlahHariInput = document.getElementById('jumlah_hari');

            function toggleBatteryInput() {
                if (!sisaGroup) return;
                const selectedOption = garageSelect.options[garageSelect.selectedIndex];
                const isElectricGarage = selectedOption?.dataset.tipe === '{{ \App\Models\Garage::TIPE_MOBIL_LISTRIK }}';

                sisaGroup.classList.toggle('hidden', !isElectricGarage);
                sisaInput.required = isElectricGarage;
                if (!isElectricGarage) sisaInput.value = '';
            }

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

            garageSelect.addEventListener('change', toggleBatteryInput);
            tipeSewaSelect.addEventListener('change', toggleDaysInput);

            toggleBatteryInput();
            toggleDaysInput();
        </script>
    @endif
@endsection