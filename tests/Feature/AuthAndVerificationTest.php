<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_penyewa_registration_sets_pending_status_and_blocks_login_until_approved(): void
    {
        $response = $this->post(route('register.submit'), [
            'nik' => '3201010101010001',
            'nama' => 'Siti Aminah',
            'no_telp' => '081234567890',
            'email' => 'siti@test.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', [
            'email' => 'siti@test.com',
            'role' => User::ROLE_PENYEWA,
            'status' => User::STATUS_PENDING,
        ]);

        // Gagal login saat masih pending
        $loginPending = $this->post(route('login.submit'), [
            'email' => 'siti@test.com',
            'password' => 'rahasia123',
        ]);
        $loginPending->assertSessionHasErrors('email');
        $this->assertGuest();

        // Admin menyetujui pendaftaran
        $admin = User::create([
            'nik' => '0000000000000001',
            'nama' => 'Admin',
            'no_telp' => '081111111111',
            'email' => 'admin@test.com',
            'password' => 'admin123',
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
        ]);

        $penyewa = User::where('email', 'siti@test.com')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.verifications.approve', $penyewa->id))
            ->assertRedirect(route('admin.verifications.index'));

        $this->assertSame(User::STATUS_APPROVED, $penyewa->fresh()->status);

        // Logout admin, lalu penyewa berhasil login
        $this->post(route('logout'));

        $this->post(route('login.submit'), [
            'email' => 'siti@test.com',
            'password' => 'rahasia123',
        ])->assertRedirect(route('penyewa.dashboard'));

        $this->assertAuthenticatedAs($penyewa);
    }

    public function test_rejected_penyewa_cannot_login_and_roles_are_isolated(): void
    {
        $admin = User::create([
            'nik' => '0000000000000001',
            'nama' => 'Admin',
            'no_telp' => '081111111111',
            'email' => 'admin@test.com',
            'password' => 'admin123',
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
        ]);

        $penyewaPending = User::create([
            'nik' => '3201010101010002',
            'nama' => 'Andi',
            'no_telp' => '081234567891',
            'email' => 'andi@test.com',
            'password' => 'rahasia123',
            'role' => User::ROLE_PENYEWA,
            'status' => User::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.verifications.reject', $penyewaPending->id))
            ->assertRedirect(route('admin.verifications.index'));

        $this->assertSame(User::STATUS_REJECTED, $penyewaPending->fresh()->status);

        // Admin tidak bisa masuk route penyewa
        $this->actingAs($admin)
            ->get(route('penyewa.dashboard'))
            ->assertForbidden();

        // Penyewa ditolak tidak bisa login
        $this->post(route('logout'));
        $this->post(route('login.submit'), [
            'email' => 'andi@test.com',
            'password' => 'rahasia123',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}