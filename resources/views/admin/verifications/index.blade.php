@extends('layouts.app')

@section('title', 'Verifikasi Pendaftar Baru')

@section('content')
    <h2 class="text-xl font-bold text-slate-900">Verifikasi Pendaftar Penyewa Baru</h2>
    <p class="mt-1 text-sm text-slate-500">Periksa data NIK (KTP) dan identitas pendaftar sebelum menerima atau menolak akun.</p>

    <h3 class="mt-6 text-base font-semibold text-slate-800">
        Menunggu Verifikasi ({{ $pendingUsers->count() }})
    </h3>

    @if ($pendingUsers->isEmpty())
        <p class="mt-2 text-sm text-slate-500">Tidak ada pendaftar baru yang menunggu verifikasi.</p>
    @else
        <div class="mt-3 overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-100 text-slate-700">
                        <th class="px-3 py-2.5 font-semibold">No</th>
                        <th class="px-3 py-2.5 font-semibold">NIK (No. KTP)</th>
                        <th class="px-3 py-2.5 font-semibold">Nama Lengkap</th>
                        <th class="px-3 py-2.5 font-semibold">No. Telepon</th>
                        <th class="px-3 py-2.5 font-semibold">Email</th>
                        <th class="px-3 py-2.5 font-semibold">Tanggal Daftar</th>
                        <th class="px-3 py-2.5 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($pendingUsers as $index => $user)
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 py-2.5">{{ $index + 1 }}</td>
                            <td class="px-3 py-2.5 font-mono text-xs">{{ $user->nik }}</td>
                            <td class="px-3 py-2.5 font-medium text-slate-900">{{ $user->nama }}</td>
                            <td class="px-3 py-2.5">{{ $user->no_telp }}</td>
                            <td class="px-3 py-2.5">{{ $user->email }}</td>
                            <td class="px-3 py-2.5 text-slate-500">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-2.5">
                                <div class="flex items-center gap-2">
                                    <form action="{{ route('admin.verifications.approve', $user->id) }}" method="POST" class="m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            type="submit"
                                            onclick="return confirm('Setujui pendaftaran {{ $user->nama }}?')"
                                            class="cursor-pointer rounded bg-emerald-600 px-3 py-1 text-xs font-semibold text-white hover:bg-emerald-700"
                                        >
                                            Terima
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.verifications.reject', $user->id) }}" method="POST" class="m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            type="submit"
                                            onclick="return confirm('Tolak pendaftaran {{ $user->nama }}?')"
                                            class="cursor-pointer rounded bg-red-600 px-3 py-1 text-xs font-semibold text-white hover:bg-red-700"
                                        >
                                            Tolak
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <h3 class="mt-8 text-base font-semibold text-slate-800">
        Riwayat Penyewa Sudah Diproses ({{ $processedUsers->count() }})
    </h3>

    @if ($processedUsers->isEmpty())
        <p class="mt-2 text-sm text-slate-500">Belum ada data penyewa yang diproses.</p>
    @else
        <div class="mt-3 overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-100 text-slate-700">
                        <th class="px-3 py-2.5 font-semibold">No</th>
                        <th class="px-3 py-2.5 font-semibold">NIK (No. KTP)</th>
                        <th class="px-3 py-2.5 font-semibold">Nama Lengkap</th>
                        <th class="px-3 py-2.5 font-semibold">No. Telepon</th>
                        <th class="px-3 py-2.5 font-semibold">Email</th>
                        <th class="px-3 py-2.5 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($processedUsers as $index => $user)
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 py-2.5">{{ $index + 1 }}</td>
                            <td class="px-3 py-2.5 font-mono text-xs">{{ $user->nik }}</td>
                            <td class="px-3 py-2.5 font-medium text-slate-900">{{ $user->nama }}</td>
                            <td class="px-3 py-2.5">{{ $user->no_telp }}</td>
                            <td class="px-3 py-2.5">{{ $user->email }}</td>
                            <td class="px-3 py-2.5">
                                @if ($user->status === \App\Models\User::STATUS_APPROVED)
                                    <span class="inline-block rounded bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">APPROVED</span>
                                @else
                                    <span class="inline-block rounded bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-800">REJECTED</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection