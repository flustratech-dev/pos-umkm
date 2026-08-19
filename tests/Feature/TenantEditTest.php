<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Product;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\RevenueSplitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantEditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Event $event;
    protected Store $store;
    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin EO',
            'username' => 'admin_tenant',
            'email' => 'admin_tenant@example.com',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $this->event = Event::create([
            'name' => 'Bazar Tenant',
            'slug' => 'bazar-tenant',
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        $this->owner = User::create([
            'name' => 'Budi Santoso',
            'username' => 'tenda-a01',
            'email' => 'tenda-a01@tenant.local',
            'phone' => '08111111111',
            'role' => 'user',
            'password' => bcrypt('password'),
        ]);

        $this->store = Store::create([
            'event_id' => $this->event->id,
            'owner_id' => $this->owner->id,
            'name' => 'Nasi Goreng Budi',
            'booth_number' => 'A01',
            'access_uuid' => (string) \Illuminate\Support\Str::uuid(),
            'is_active' => true,
        ]);

        $this->owner->update(['store_id' => $this->store->id]);
    }

    protected function payload(array $overrides = []): array
    {
        return array_merge([
            'owner_name' => 'Budi Santoso',
            'store_name' => 'Nasi Goreng Budi',
            'booth_code' => 'A01',
            'phone' => '08111111111',
        ], $overrides);
    }

    public function test_admin_can_edit_a_registered_tenant(): void
    {
        $this->actingAs($this->admin)->putJson(
            route('admin.events.update-tenant', [$this->event, $this->store]),
            $this->payload([
                'owner_name' => 'Budi Hartono',
                'store_name' => 'Nasi Goreng Budi Jaya',
                'booth_code' => '019',
                'phone' => '08222222222',
            ])
        )->assertOk()->assertJson(['success' => true]);

        $this->store->refresh();
        $this->owner->refresh();

        $this->assertEquals('Nasi Goreng Budi Jaya', $this->store->name);
        $this->assertEquals('019', $this->store->booth_number);
        $this->assertEquals('Budi Hartono', $this->owner->name);
        $this->assertEquals('08222222222', $this->owner->phone);
    }

    public function test_editing_a_tenant_keeps_its_access_link(): void
    {
        $uuid = $this->store->access_uuid;

        $this->actingAs($this->admin)->putJson(
            route('admin.events.update-tenant', [$this->event, $this->store]),
            $this->payload(['store_name' => 'Nama Baru'])
        )->assertOk();

        $this->assertEquals($uuid, $this->store->fresh()->access_uuid);

        // Link lama harus tetap bisa dipakai tenant.
        $this->get(route('tenant.access', ['uuid' => $uuid]))->assertRedirect(route('user.kasir'));
    }

    public function test_booth_code_stays_unique_within_the_event(): void
    {
        $otherOwner = User::create([
            'name' => 'Siti',
            'username' => 'tenda-a02',
            'email' => 'tenda-a02@tenant.local',
            'role' => 'user',
            'password' => bcrypt('password'),
        ]);

        Store::create([
            'event_id' => $this->event->id,
            'owner_id' => $otherOwner->id,
            'name' => 'Es Teh Siti',
            'booth_number' => 'A02',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)->putJson(
            route('admin.events.update-tenant', [$this->event, $this->store]),
            $this->payload(['booth_code' => 'A02'])
        )->assertStatus(422)->assertJsonValidationErrors('booth_code');

        $this->assertEquals('A01', $this->store->fresh()->booth_number);
    }

    public function test_keeping_the_same_booth_code_is_allowed(): void
    {
        $this->actingAs($this->admin)->putJson(
            route('admin.events.update-tenant', [$this->event, $this->store]),
            $this->payload(['store_name' => 'Nasi Goreng Budi Spesial'])
        )->assertOk();

        $this->assertEquals('Nasi Goreng Budi Spesial', $this->store->fresh()->name);
    }

    public function test_the_same_booth_code_may_exist_in_another_event(): void
    {
        $otherEvent = Event::create([
            'name' => 'Bazar Lain',
            'slug' => 'bazar-lain',
            'is_active' => false,
            'created_by' => $this->admin->id,
        ]);

        Store::create([
            'event_id' => $otherEvent->id,
            'owner_id' => $this->admin->id,
            'name' => 'Stand Event Lain',
            'booth_number' => 'B07',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)->putJson(
            route('admin.events.update-tenant', [$this->event, $this->store]),
            $this->payload(['booth_code' => 'B07'])
        )->assertOk();

        $this->assertEquals('B07', $this->store->fresh()->booth_number);
    }

    public function test_tenant_from_another_event_cannot_be_edited_here(): void
    {
        $otherEvent = Event::create([
            'name' => 'Bazar Lain',
            'slug' => 'bazar-lain-2',
            'is_active' => false,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)->putJson(
            route('admin.events.update-tenant', [$otherEvent, $this->store]),
            $this->payload()
        )->assertStatus(422)->assertJson(['success' => false]);

        $this->assertEquals('Nasi Goreng Budi', $this->store->fresh()->name);
    }

    public function test_new_booth_code_drives_the_qris_unique_code_right_away(): void
    {
        $product = Product::create([
            'store_id' => $this->store->id,
            'title' => 'Nasi Goreng',
            'price' => 10000,
            'category' => 'Makanan',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)->putJson(
            route('admin.events.update-tenant', [$this->event, $this->store]),
            $this->payload(['booth_code' => '019'])
        )->assertOk();

        $service = new CheckoutService(new RevenueSplitService());
        $tx = $service->processQrisCheckout($this->store->fresh(), $this->owner, [
            ['product_id' => $product->id, 'qty' => 1],
        ]);

        $this->assertEquals(10019, (float) $tx->total_amount);
    }

    public function test_editing_a_tenant_keeps_its_products_and_transactions(): void
    {
        $product = Product::create([
            'store_id' => $this->store->id,
            'title' => 'Nasi Goreng',
            'price' => 10000,
            'category' => 'Makanan',
            'is_active' => true,
        ]);

        $transaction = Transaction::create([
            'invoice_code' => 'INV-TENANT',
            'store_id' => $this->store->id,
            'cashier_id' => $this->owner->id,
            'total_amount' => 10000,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        $this->actingAs($this->admin)->putJson(
            route('admin.events.update-tenant', [$this->event, $this->store]),
            $this->payload(['store_name' => 'Warung Budi', 'booth_code' => '019'])
        )->assertOk();

        $this->assertEquals($this->store->id, $product->fresh()->store_id);
        $this->assertEquals($this->store->id, $transaction->fresh()->store_id);
        $this->assertEquals($this->store->id, $this->owner->fresh()->store_id);
    }

    public function test_edit_modal_lives_inside_the_alpine_scope(): void
    {
        // Modal sempat ditempel di luar <div x-data>, akibatnya seluruh isian
        // kosong dan modalnya tidak pernah terbuka. Kunci posisinya di sini.
        $source = file_get_contents(resource_path('views/admin/event-detail.blade.php'));

        $modalAt = strpos($source, '<!-- EDIT TENANT MODAL -->');
        $rootClosesAt = strpos($source, "
</div>");

        $this->assertNotFalse($modalAt, 'Blok modal edit tenant tidak ditemukan.');
        $this->assertNotFalse($rootClosesAt, 'Penutup root x-data tidak ditemukan.');
        $this->assertLessThan($rootClosesAt, $modalAt, 'Modal edit tenant harus berada di dalam root x-data.');
    }

    public function test_edit_button_is_rendered_for_each_tenant(): void
    {
        $this->actingAs($this->admin)->get(route('admin.events.detail', $this->event))
            ->assertOk()
            ->assertSee('Edit Tenant')
            ->assertSee('openEditTenant', false);
    }

    public function test_updated_tenant_shows_up_across_the_other_pages(): void
    {
        $this->actingAs($this->admin)->putJson(
            route('admin.events.update-tenant', [$this->event, $this->store]),
            $this->payload(['store_name' => 'Warung Budi Jaya', 'booth_code' => '019'])
        )->assertOk();

        // Halaman detail event
        $this->actingAs($this->admin)->get(route('admin.events.detail', $this->event))
            ->assertOk()
            ->assertSee('Warung Budi Jaya')
            ->assertSee('019');

        // Halaman kelola warung
        $this->actingAs($this->admin)->get(route('admin.warung'))
            ->assertOk()
            ->assertSee('Warung Budi Jaya');

        // Payload kasir milik tenant: kode unik ikut kode tenda baru
        $html = $this->actingAs($this->owner->fresh())->get(route('user.kasir'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('"unique_code":19', $html);
        $this->assertStringContainsString('Warung Budi Jaya', $html);
    }
}
