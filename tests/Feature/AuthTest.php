<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::create([
            'name' => 'Event Aktif',
            'slug' => 'event-aktif',
            'is_active' => true,
        ]);
    }

    public function test_user_registration_creates_user_and_store(): void
    {
        $response = $this->post('/register', [
            'name' => 'Pak Joko',
            'store_name' => 'Warung Sate Mas Joko',
            'username' => 'sate.joko',
            'email' => 'joko@gmail.com',
            'phone' => '08123456789',
            'booth_number' => 'Stand C-01',
            'category' => 'Makanan',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/user/kasir');
        $this->assertAuthenticated();

        $user = User::where('email', 'joko@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('user', $user->role);
        $this->assertNotNull($user->store);
        $this->assertEquals('Warung Sate Mas Joko', $user->store->name);
    }

    public function test_login_and_role_redirection(): void
    {
        $admin = User::create([
            'name' => 'Admin EO',
            'username' => 'admin.eo',
            'email' => 'admin@pos-umkm.id',
            'role' => 'admin',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'login' => 'admin@pos-umkm.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }
}
