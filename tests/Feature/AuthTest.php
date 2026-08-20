<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = Event::create([
            'name' => 'Event Aktif',
            'slug' => 'event-aktif',
            'is_active' => true,
        ]);
    }

    public function test_tenant_access_via_uuid_link(): void
    {
        $owner = User::create([
            'name' => 'Pak Joko',
            'username' => 'tenda-c01',
            'email' => 'tenda-c01@tenant.local',
            'role' => 'user',
            'password' => bcrypt('secret'),
        ]);

        $uuid = (string) Str::uuid();

        $store = Store::create([
            'event_id' => $this->event->id,
            'owner_id' => $owner->id,
            'name' => 'Warung Sate Mas Joko',
            'booth_number' => 'Stand C-01',
            'access_uuid' => $uuid,
            'category' => 'Makanan',
            'is_active' => true,
        ]);

        $owner->update(['store_id' => $store->id]);

        $response = $this->get('/tenda/' . $uuid);

        $response->assertRedirect(route('user.kasir'));
        $this->assertAuthenticatedAs($owner);
    }

    public function test_tenant_access_fails_when_event_is_inactive(): void
    {
        $this->event->update(['is_active' => false]);

        $owner = User::create([
            'name' => 'Pak Joko',
            'username' => 'tenda-c01',
            'email' => 'tenda-c01@tenant.local',
            'role' => 'user',
            'password' => bcrypt('secret'),
        ]);

        $uuid = (string) Str::uuid();

        $store = Store::create([
            'event_id' => $this->event->id,
            'owner_id' => $owner->id,
            'name' => 'Warung Sate Mas Joko',
            'booth_number' => 'Stand C-01',
            'access_uuid' => $uuid,
            'category' => 'Makanan',
            'is_active' => true,
        ]);

        $response = $this->get('/tenda/' . $uuid);

        $response->assertOk();
        $response->assertViewIs('tenant.event-expired');
        $this->assertGuest();
    }

    public function test_login_and_role_redirection(): void
    {
        $admin = User::create([
            'name' => 'Admin EO',
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'role' => 'admin',
            'password' => bcrypt('12345678'),
        ]);

        $response = $this->post('/login', [
            'login' => 'admin@gmail.com',
            'password' => '12345678',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_and_superadmin_can_login_with_their_username(): void
    {
        $admin = User::create([
            'name' => 'Admin EO',
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'role' => 'admin',
            'password' => bcrypt('12345678'),
        ]);

        $this->post('/login', [
            'login' => 'admin',
            'password' => '12345678',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);

        $this->post('/logout');

        $superAdmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'email' => 'superadmin@gmail.com',
            'role' => 'superadmin',
            'password' => bcrypt('12345678'),
        ]);

        $this->post('/login', [
            'login' => 'superadmin',
            'password' => '12345678',
        ])->assertRedirect(route('superadmin.dashboard'));

        $this->assertAuthenticatedAs($superAdmin);
    }

    public function test_login_form_accepts_a_username_not_only_an_email(): void
    {
        // Kolom bertipe email membuat browser memblokir submit saat diisi username,
        // padahal backend menerima username maupun email.
        $html = $this->get(route('login'))->assertOk()->getContent();

        $this->assertStringNotContainsString('type="email"', $html);
        $this->assertStringContainsString('Email atau Username', $html);
    }
}
