<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_halaman_publik_terbuka_untuk_tamu(): void
    {
        $this->get('/')->assertOk();
        $this->get('/lapangan')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }

    public function test_tamu_diarahkan_ke_login_saat_mengakses_halaman_private(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/reservasi')->assertRedirect('/login');
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/pemilik/dashboard')->assertRedirect('/login');
    }

    public function test_pendaftaran_membuat_akun_pelanggan_dan_langsung_login(): void
    {
        $response = $this->post('/register', [
            'name' => 'Pengguna Baru',
            'email' => 'baru@example.test',
            'telepon' => '08123456789',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'baru@example.test')->first();
        $this->assertNotNull($user);
        $this->assertSame('pelanggan', $user->role, 'Akun daftar wajib berperan pelanggan.');
        $this->assertTrue(
            \Illuminate\Support\Facades\Hash::check('rahasia123', $user->password),
            'Password harus disimpan dalam bentuk hash.'
        );
    }

    public function test_pendaftaran_ditolak_jika_email_sudah_terpakai(): void
    {
        User::factory()->create(['email' => 'sudah@example.test']);

        $response = $this->from('/register')->post('/register', [
            'name' => 'Duplikat',
            'email' => 'sudah@example.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('email');
        $this->assertSame(1, User::where('email', 'sudah@example.test')->count());
        $this->assertGuest();
    }

    public function test_pendaftaran_ditolak_jika_konfirmasi_password_berbeda(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Typo Password',
            'email' => 'typo@example.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia999',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'typo@example.test']);
    }

    public function test_login_dengan_kredensial_benar_untuk_setiap_role(): void
    {
        foreach (['pelanggan', 'admin', 'pemilik'] as $role) {
            $user = User::factory()->create([
                'role' => $role,
                'email' => $role.'@example.test',
                'password' => 'rahasia123',
            ]);

            $response = $this->post('/login', [
                'email' => $user->email,
                'password' => 'rahasia123',
            ]);

            $response->assertRedirect('/dashboard');
            $this->assertAuthenticatedAs($user);

            $this->post('/logout');
            $this->assertGuest();
        }
    }

    public function test_login_ditolak_saat_password_salah(): void
    {
        User::factory()->create([
            'email' => 'salah@example.test',
            'password' => 'rahasia123',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'salah@example.test',
            'password' => 'password-keliru',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_logout_mengakhiri_sesi(): void
    {
        $user = $this->pengguna();

        $this->actingAs($user)->post('/logout')->assertRedirect('/');

        $this->assertGuest();
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_halaman_auth_diarahkan_ke_dashboard_bila_sudah_login(): void
    {
        $this->actingAs($this->pengguna())
            ->get('/login')
            ->assertRedirect('/dashboard');

        $this->actingAs($this->pengguna())
            ->get('/register')
            ->assertRedirect('/dashboard');
    }
}