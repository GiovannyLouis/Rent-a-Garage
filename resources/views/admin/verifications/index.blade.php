@extends('layouts.app')

@section('title', 'Verifikasi Pendaftar Baru')

@section('content')
    <h2>Verifikasi Pendaftar Penyewa Baru</h2>
    <p class="text-muted">Periksa data NIK (KTP) dan identitas pendaftar sebelum menerima atau menolak akun.</p>

    <h3>Menunggu Verifikasi ({{ $pendingUsers->count() }})</h3>

    @if ($pendingUsers->isEmpty())
        <p class="text-muted">Tidak ada pendaftar baru yang menunggu verifikasi.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIK (No. KTP)</th>
                    <th>Nama Lengkap</th>
                    <th>No. Telepon</th>
                    <th>Email</th>
                    <th>Tanggal Daftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pendingUsers as $index => $user)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $user->nik }}</td>
                        <td>{{ $user->nama }}</td>
                        <td>{{ $user->no_telp }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <form action="{{ route('admin.verifications.approve', $user->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-success" onclick="return confirm('Setujui pendaftaran {{ $user->nama }}?')">
                                        Terima
                                    </button>
                                </form>

                                <form action="{{ route('admin.verifications.reject', $user->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-danger-sm" onclick="return confirm('Tolak pendaftaran {{ $user->nama }}?')">
                                        Tolak
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h3>Riwayat Penyewa Sudah Diproses ({{ $processedUsers->count() }})</h3>

    @if ($processedUsers->isEmpty())
        <p class="text-muted">Belum ada data penyewa yang diproses.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIK (No. KTP)</th>
                    <th>Nama Lengkap</th>
                    <th>No. Telepon</th>
                    <th>Email</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($processedUsers as $index => $user)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $user->nik }}</td>
                        <td>{{ $user->nama }}</td>
                        <td>{{ $user->no_telp }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if ($user->status === \App\Models\User::STATUS_APPROVED)
                                <span class="badge badge-approved">APPROVED</span>
                            @else
                                <span class="badge badge-rejected">REJECTED</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection