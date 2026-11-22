@extends('layouts.app')

@section('title', 'Kelola Garasi')

@section('content')
    <h2 class="text-xl font-bold text-slate-900">Kelola Garasi</h2>

    {{-- Bagian Atas: Form Buat Garasi Baru (Batch) --}}
    <div class="mt-4 rounded-md border border-slate-200 bg-slate-50 p-5">
        <h3 class="text-base font-semibold text-slate-900">Buat Garasi Baru</h3>

        <form action="{{ route('admin.garages.store') }}" method="POST" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            @csrf

            <div>
                <label for="tipe_garasi" class="mb-1 block text-sm font-medium text-slate-700">Tipe Garasi</label>
                <select
                    name="tipe_garasi"
                    id="tipe_garasi"
                    required
                    class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
                    <option value="">-- Pilih Tipe Garasi --</option>
                    <option value="{{ \App\Models\Garage::TIPE_SEPEDA }}" @selected(old('tipe_garasi') === \App\Models\Garage::TIPE_SEPEDA)>
                        Garasi Sepeda (Kode S) - Sisa kuota: {{ $remainingQuota[\App\Models\Garage::TIPE_SEPEDA] }}
                    </option>
                    <option value="{{ \App\Models\Garage::TIPE_MOBIL }}" @selected(old('tipe_garasi') === \App\Models\Garage::TIPE_MOBIL)>
                        Garasi Mobil (Kode C) - Sisa kuota: {{ $remainingQuota[\App\Models\Garage::TIPE_MOBIL] }}
                    </option>
                    <option value="{{ \App\Models\Garage::TIPE_MOBIL_LISTRIK }}" @selected(old('tipe_garasi') === \App\Models\Garage::TIPE_MOBIL_LISTRIK)>
                        Garasi Mobil Listrik (Kode E) - Sisa kuota: {{ $remainingQuota[\App\Models\Garage::TIPE_MOBIL_LISTRIK] }}
                    </option>
                </select>
            </div>

            <div>
                <label for="jumlah" class="mb-1 block text-sm font-medium text-slate-700">Jumlah Garasi</label>
                <input
                    type="number"
                    id="jumlah"
                    name="jumlah"
                    value="{{ old('jumlah', 1) }}"
                    min="1"
                    max="999"
                    required
                    class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
            </div>

            <div>
                <label for="harga_harian" class="mb-1 block text-sm font-medium text-slate-700">Harga Sewa Harian (Rp)</label>
                <input
                    type="number"
                    id="harga_harian"
                    name="harga_harian"
                    value="{{ old('harga_harian') }}"
                    min="1"
                    required
                    class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
                <p class="mt-1 text-xs text-slate-500" id="preview_bulanan">Harga sewa bulanan otomatis (25×): Rp 0</p>
            </div>

            <div id="kwh_group" @class(['hidden' => old('tipe_garasi') !== \App\Models\Garage::TIPE_MOBIL_LISTRIK])>
                <label for="harga_kwh" class="mb-1 block text-sm font-medium text-slate-700">Harga Isi Listrik per kWh (Rp)</label>
                <input
                    type="number"
                    id="harga_kwh"
                    name="harga_kwh"
                    value="{{ old('harga_kwh') }}"
                    min="1"
                    class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
            </div>

            <div class="sm:col-span-2">
                <button type="submit" class="cursor-pointer rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Simpan Garasi
                </button>
            </div>
        </form>
    </div>

    {{-- Ringkasan Jumlah per Tipe --}}
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-md border border-slate-200 p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Garasi Sepeda (S)</p>
            <p class="mt-1 text-lg font-bold text-slate-900">{{ $summary[\App\Models\Garage::TIPE_SEPEDA] }} / 999 unit</p>
        </div>
        <div class="rounded-md border border-slate-200 p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Garasi Mobil (C)</p>
            <p class="mt-1 text-lg font-bold text-slate-900">{{ $summary[\App\Models\Garage::TIPE_MOBIL] }} / 999 unit</p>
        </div>
        <div class="rounded-md border border-slate-200 p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Garasi Mobil Listrik (E)</p>
            <p class="mt-1 text-lg font-bold text-slate-900">{{ $summary[\App\Models\Garage::TIPE_MOBIL_LISTRIK] }} / 999 unit</p>
        </div>
    </div>

    {{-- Bagian Bawah: Tabel Daftar Garasi --}}
    <h3 class="mt-6 text-base font-semibold text-slate-800">Daftar Garasi ({{ $garages->total() }} unit)</h3>

    @if ($garages->isEmpty())
        <p class="mt-2 text-sm text-slate-500">Belum ada data garasi.</p>
    @else
        <div class="mt-3 overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-100 text-slate-700">
                        <th class="px-3 py-2.5 font-semibold">Kode</th>
                        <th class="px-3 py-2.5 font-semibold">Tipe Garasi</th>
                        <th class="px-3 py-2.5 font-semibold">Harga Harian</th>
                        <th class="px-3 py-2.5 font-semibold">Harga Bulanan (25×)</th>
                        <th class="px-3 py-2.5 font-semibold">Harga Listrik / kWh</th>
                        <th class="px-3 py-2.5 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($garages as $garage)
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 py-2.5 font-bold text-slate-900">{{ $garage->kode }}</td>
                            <td class="px-3 py-2.5">{{ $garage->getLabelTipe() }}</td>
                            <td class="px-3 py-2.5">Rp {{ number_format($garage->harga_harian, 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5">Rp {{ number_format($garage->getHargaBulanan(), 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5">
                                @if ($garage->harga_kwh !== null)
                                    Rp {{ number_format($garage->harga_kwh, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-3 py-2.5">
                                @if ($garage->status === \App\Models\Garage::STATUS_AVAILABLE)
                                    <span class="inline-block rounded bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">AVAILABLE</span>
                                @else
                                    <span class="inline-block rounded bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-800">USED</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $garages->links() }}
        </div>
    @endif

    <script>
        const tipeSelect = document.getElementById('tipe_garasi');
        const kwhGroup = document.getElementById('kwh_group');
        const kwhInput = document.getElementById('harga_kwh');
        const hargaHarianInput = document.getElementById('harga_harian');
        const previewBulanan = document.getElementById('preview_bulanan');

        function toggleKwhField() {
            const isElectric = tipeSelect.value === '{{ \App\Models\Garage::TIPE_MOBIL_LISTRIK }}';
            kwhGroup.classList.toggle('hidden', !isElectric);
            kwhInput.required = isElectric;

            if (!isElectric) {
                kwhInput.value = '';
            }
        }

        function updateMonthlyPreview() {
            const harian = parseInt(hargaHarianInput.value, 10) || 0;
            const bulanan = harian * 25;
            previewBulanan.textContent = 'Harga sewa bulanan otomatis (25×): Rp ' + bulanan.toLocaleString('id-ID');
        }

        tipeSelect.addEventListener('change', toggleKwhField);
        hargaHarianInput.addEventListener('input', updateMonthlyPreview);

        toggleKwhField();
        updateMonthlyPreview();
    </script>
@endsection