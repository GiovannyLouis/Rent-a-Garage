<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman form registrasi Penyewa baru.
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Memproses pendaftaran Penyewa baru dengan status awal pending.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'digits:16', 'unique:users,nik'],
            'nama' => ['required', 'string', 'max:255'],
            'no_telp' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus terdiri dari tepat 16 digit angka.',
            'nik.unique' => 'NIK sudah terdaftar di sistem.',
            'nama.required' => 'Nama lengkap wajib diisi.',
            'no_telp.required' => 'Nomor telepon wajib diisi.',
            'no_telp.regex' => 'Format nomor telepon tidak valid (8-20 karakter).',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar di sistem.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        User::create([
            'nik' => $validated['nik'],
            'nama' => $validated['nama'],
            'no_telp' => $validated['no_telp'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_PENYEWA,
            'status' => User::STATUS_PENDING,
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Registrasi berhasil! Akun Anda menunggu verifikasi Admin sebelum dapat login.');
    }

    /**
     * Menampilkan halaman form login.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Memproses autentikasi pengguna (Admin maupun Penyewa).
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'Email atau password salah.'])
                ->onlyInput('email');
        }

        if ($user->status === User::STATUS_PENDING) {
            return back()
                ->withErrors(['email' => 'Akun Anda masih menunggu verifikasi Admin.'])
                ->onlyInput('email');
        }

        if ($user->status === User::STATUS_REJECTED) {
            return back()
                ->withErrors(['email' => 'Pendaftaran akun Anda ditolak oleh Admin.'])
                ->onlyInput('email');
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->role === User::ROLE_ADMIN) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('penyewa.dashboard'));
    }

    /**
     * Memproses logout pengguna dan membersihkan session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Anda telah berhasil logout.');
    }
}