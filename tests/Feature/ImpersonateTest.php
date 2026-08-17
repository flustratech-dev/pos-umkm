<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpersonateTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;
    protected User $admin;
    protected User $tenant;
    protected Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = Event::create([
            'name' => 'Event Bazar 2026',
            'slug' => 'event-bazar-2026',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Panitia EO',
            'username' => 'admin.eo',
            'email' => 'admin@eo.com',
            'role' => 'admin',
            'password' => bcrypt('password123'),
        ]);

        $this->tenant = User::create([
            'name' => 'Budi Tenant',
            'username' => 'budi.stand',
            'email' => 'budi@gmail.com',
            'role' => 'user',
            'password' => bcrypt('password123'),
        ]);

        $this->store = Store::create([
            'event_id' => $this->event->id,
            'owner_id' => $this->tenant->id,
            'name' => 'Kedai Kopi Budi',
            'booth_number' => 'Stand 01',
            'is_active' => true,
        ]);

        $this->tenant->update(['store_id' => $this->store->id]);
    }

    public function test_admin_can_impersonate_store_owner_and_leave(): void
    {
        // 1. Login as Admin
        $this->actingAs($this->admin);

        // 2. Start Impersonation
        $response = $this->post(route('admin.impersonate', $this->store->id));

        $response->assertRedirect(route('user.kasir'));
        $response->assertSessionHas('impersonator_id', $this->admin->id);
        $response->assertSessionHas('impersonator_name', $this->admin->name);
        $response->assertSessionHas('impersonator_role', 'admin');

        // Current authenticated user should now be the tenant
        $this->assertEquals($this->tenant->id, auth()->id());
        $this->assertEquals('user', auth()->user()->role);

        // 3. Impersonated user can access user routes
        $kasirResponse = $this->get(route('user.kasir'));
        $kasirResponse->assertOk();

        // 4. Leave Impersonation
        $leaveResponse = $this->post(route('impersonate.leave'));
        $leaveResponse->assertRedirect(route('admin.warung'));
        $leaveResponse->assertSessionMissing('impersonator_id');

        // Authenticated user should be restored to Admin
        $this->assertEquals($this->admin->id, auth()->id());
        $this->assertEquals('admin', auth()->user()->role);
    }

    public function test_regular_user_cannot_impersonate(): void
    {
        $otherTenant = User::create([
            'name' => 'Other Tenant',
            'username' => 'other.stand',
            'email' => 'other@gmail.com',
            'role' => 'user',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($otherTenant);

        $response = $this->post(route('admin.impersonate', $this->store->id));
        $response->assertRedirect(route('user.kasir'));
        $this->assertEquals($otherTenant->id, auth()->id());
    }

    public function test_admin_accessing_tenant_uuid_link_activates_inspection_mode(): void
    {
        $this->store->update(['access_uuid' => 'test-uuid-1234']);

        // 1. Admin is authenticated
        $this->actingAs($this->admin);

        // 2. Admin opens tenant link
        $response = $this->get(route('tenant.access', ['uuid' => 'test-uuid-1234']));

        $response->assertRedirect(route('user.kasir'));
        $response->assertSessionHas('impersonator_id', $this->admin->id);

        // Current session is tenant
        $this->assertEquals($this->tenant->id, auth()->id());

        // 3. Leave inspection mode
        $leaveResponse = $this->post(route('impersonate.leave'));
        $leaveResponse->assertRedirect(route('admin.warung'));

        // Restored to Admin
        $this->assertEquals($this->admin->id, auth()->id());
    }

    public function test_tenant_user_visiting_login_page_clears_tenant_session(): void
    {
        // 1. Tenant is logged in
        $this->actingAs($this->tenant);

        // 2. Visits login page
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertViewIs('auth.login');

        // Session was cleared
        $this->assertNull(auth()->user());
    }
}
