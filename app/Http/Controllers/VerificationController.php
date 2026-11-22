<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VerificationController extends Controller
{
    /**
     * Menampilkan daftar penyewa baru yang menunggu verifikasi
     * beserta riwayat penyewa yang sudah diproses.
     */
    public function index(): View
    {
        $pendingUsers = User::where('role', User::ROLE_PENYEWA)
            ->where('status', User::STATUS_PENDING)
            ->latest()
            ->get();

        $processedUsers = User::where('role', User::ROLE_PENYEWA)
            ->whereIn('status', [User::STATUS_APPROVED, User::STATUS_REJECTED])
            ->latest()
            ->get();

        return view('admin.verifications.index', compact('pendingUsers', 'processedUsers'));
    }

    /**
     * Menyetujui pendaftaran penyewa baru setelah pengecekan data/KTP (NIK).
     */
    public function approve(int $userId): RedirectResponse
    {
        $user = User::where('role', User::ROLE_PENYEWA)->findOrFail($userId);

        if ($user->status !== User::STATUS_PENDING) {
            return redirect()
                ->route('admin.verifications.index')
                ->withErrors(['status' => 'Status penyewa ini sudah diproses sebelumnya.']);
        }

        $user->update([
            'status' => User::STATUS_APPROVED,
        ]);

        return redirect()
            ->route('admin.verifications.index')
            ->with('success', "Pendaftaran penyewa {$user->nama} (NIK: {$user->nik}) berhasil disetujui.");
    }

    /**
     * Menolak pendaftaran penyewa baru.
     */
    public function reject(int $userId): RedirectResponse
    {
        $user = User::where('role', User::ROLE_PENYEWA)->findOrFail($userId);

        if ($user->status !== User::STATUS_PENDING) {
            return redirect()
                ->route('admin.verifications.index')
                ->withErrors(['status' => 'Status penyewa ini sudah diproses sebelumnya.']);
        }

        $user->update([
            'status' => User::STATUS_REJECTED,
        ]);

        return redirect()
            ->route('admin.verifications.index')
            ->with('success', "Pendaftaran penyewa {$user->nama} (NIK: {$user->nik}) telah ditolak.");
    }
}