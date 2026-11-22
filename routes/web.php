<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\GarageController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ReportController;
use App\Models\Rental;
use App\Models\Garage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Halaman utama diarahkan sesuai status login & role
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->role === User::ROLE_ADMIN
            ? redirect()->route('admin.dashboard')
            : redirect()->route('penyewa.dashboard');
    }

    return redirect()->route('login');
})->name('home');

// Route untuk pengguna yang belum login (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

// Route Logout (hanya untuk yang sudah login)
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Route khusus Admin
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', function () {
            $pendingCount = User::where('role', User::ROLE_PENYEWA)
                ->where('status', User::STATUS_PENDING)
                ->count();

            $totalGarages = Garage::count();
            $availableGarages = Garage::where('status', Garage::STATUS_AVAILABLE)->count();

            $today = now()->toDateString();
            $activeRentalsCount = Rental::whereDate('tanggal_mulai', '<=', $today)
                ->whereDate('tanggal_selesai', '>=', $today)
                ->whereHas('garage', fn ($q) => $q->where('status', Garage::STATUS_USED))
                ->count();

            return view('admin.dashboard', compact(
                'pendingCount',
                'totalGarages',
                'availableGarages',
                'activeRentalsCount'
            ));
        })->name('dashboard');

        // Verifikasi Pendaftar
        Route::get('/verifications', [VerificationController::class, 'index'])
            ->name('verifications.index');
        Route::patch('/verifications/{userId}/approve', [VerificationController::class, 'approve'])
            ->name('verifications.approve');
        Route::patch('/verifications/{userId}/reject', [VerificationController::class, 'reject'])
            ->name('verifications.reject');

        // Manajemen Garasi
        Route::get('/garages', [GarageController::class, 'index'])
            ->name('garages.index');
        Route::post('/garages', [GarageController::class, 'storeBatch'])
            ->name('garages.store');

        // Perpanjang Status Garasi
        Route::get('/rentals', [RentalController::class, 'adminIndex'])
            ->name('rentals.index');
        Route::get('/rentals/{rentalId}/extend', [RentalController::class, 'extendForm'])
            ->name('rentals.extend.form');
        Route::get('/rentals/{rentalId}/extend/confirm', [RentalController::class, 'extendConfirm'])
            ->name('rentals.extend.confirm');
        Route::post('/rentals/{rentalId}/extend', [RentalController::class, 'extend'])
            ->name('rentals.extend');

        // Laporan Sewa Garasi
        Route::get('/reports', [ReportController::class, 'index'])
            ->name('reports.index');
    });

// Route khusus Penyewa
Route::middleware(['auth', 'role:penyewa'])
    ->prefix('penyewa')
    ->name('penyewa.')
    ->group(function () {
        Route::get('/dashboard', function () {
            $activeRentalsCount = Rental::where('user_id', Auth::id())
                ->whereDate('tanggal_selesai', '>=', now()->toDateString())
                ->count();

            $totalRentalsCount = Rental::where('user_id', Auth::id())->count();

            return view('penyewa.dashboard', compact('activeRentalsCount', 'totalRentalsCount'));
        })->name('dashboard');

        Route::get('/rentals', [RentalController::class, 'index'])->name('rentals.index');

        // Form sewa bertahap 3 halaman
        Route::get('/rentals/create', [RentalController::class, 'create'])->name('rentals.create');
        Route::post('/rentals/step-1', [RentalController::class, 'storeStep1'])->name('rentals.step1');
        Route::get('/rentals/step-2', [RentalController::class, 'step2'])->name('rentals.step2');
        Route::post('/rentals/step-2', [RentalController::class, 'storeStep2'])->name('rentals.step2.store');
        Route::get('/rentals/step-3', [RentalController::class, 'step3'])->name('rentals.step3');
        Route::post('/rentals', [RentalController::class, 'store'])->name('rentals.store');
    });