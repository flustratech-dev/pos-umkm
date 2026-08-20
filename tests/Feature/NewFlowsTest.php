<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Product;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewFlowsTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin EO',
            'username' => 'admin.eo',
            'email' => 'admin@eo.com',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $this->event = Event::create([
            'name' => 'Bazar Kuliner 2026',
            'slug' => 'bazar-kuliner-2026',
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_view_event_detail_and_register_tenant(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.events.detail', $this->event));
        $response->assertOk();
        $response->assertViewIs('admin.event-detail');

        // Admin registers a new tenant (nama, nama warung, kode tenda)
        $regResponse = $this->actingAs($this->admin)->post(route('admin.events.register-tenant', $this->event), [
            'owner_name' => 'Ibu Siti',
            'store_name' => 'Warung Bakso Siti',
            'booth_code' => 'T-01',
        ]);

        $regResponse->assertRedirect(route('admin.events.detail', $this->event));

        $store = Store::where('name', 'Warung Bakso Siti')->first();
        $this->assertNotNull($store);
        $this->assertEquals('T-01', $store->booth_number);
        $this->assertNotNull($store->access_uuid);
        $this->assertNotNull($store->owner);
        $this->assertEquals('Ibu Siti', $store->owner->name);
    }

    public function test_cannot_register_duplicate_booth_code_in_same_event(): void
    {
        // Register first tenant with booth code T-01
        $this->actingAs($this->admin)->post(route('admin.events.register-tenant', $this->event), [
            'owner_name' => 'Ibu Siti',
            'store_name' => 'Warung Bakso Siti',
            'booth_code' => 'T-01',
        ]);

        // Attempt to register second tenant with same booth code T-01 in the same event
        $duplicateResponse = $this->actingAs($this->admin)->postJson(route('admin.events.register-tenant', $this->event), [
            'owner_name' => 'Pak Joko',
            'store_name' => 'Warung Nasi Goreng',
            'booth_code' => 'T-01',
        ]);

        $duplicateResponse->assertStatus(422);
        $duplicateResponse->assertJsonValidationErrors(['booth_code']);
    }

    public function test_admin_can_view_and_confirm_cash_verification(): void
    {
        $owner = User::create([
            'name' => 'Pak Budi',
            'username' => 'tenda-b01',
            'email' => 'tenda-b01@tenant.local',
            'role' => 'user',
            'password' => bcrypt('secret'),
        ]);

        $store = Store::create([
            'event_id' => $this->event->id,
            'owner_id' => $owner->id,
            'name' => 'Warung Mie Ayam',
            'booth_number' => 'B-01',
            'access_uuid' => 'test-uuid-1234',
            'is_active' => true,
        ]);

        $product = Product::create([
            'store_id' => $store->id,
            'title' => 'Mie Ayam Bakso',
            'price' => 15000,
            'is_active' => true,
        ]);

        // Create pending cash transaction from checkout
        $tx = Transaction::create([
            'invoice_code' => 'INV-TEST-CASH-001',
            'store_id' => $store->id,
            'cashier_id' => $owner->id,
            'total_amount' => 30000,
            'payment_method' => 'cash',
            'amount_paid' => 50000,
            'change_due' => 20000,
            'status' => 'pending',
        ]);

        // Admin views cash verification list
        $viewResponse = $this->actingAs($this->admin)->get(route('admin.verifikasi-cash.index'));
        $viewResponse->assertOk();
        $viewResponse->assertViewIs('admin.verifikasi-cash');

        // Admin confirms cash payment at exit cashier
        $confirmResponse = $this->actingAs($this->admin)->post(route('admin.verifikasi-cash.confirm', $tx));
        $confirmResponse->assertRedirect(route('admin.verifikasi-cash.index'));

        $tx->refresh();
        $this->assertEquals('paid', $tx->status);
        $this->assertEquals($this->admin->id, $tx->verified_by);
        $this->assertNotNull($tx->revenueSplit);
        $this->assertEquals(22500.00, (float) $tx->revenueSplit->owner_share); // 75%
        $this->assertEquals(6750.00, (float) $tx->revenueSplit->admin_gross_share); // 22.5%
        $this->assertEquals(750.00, (float) $tx->revenueSplit->superadmin_share); // 2.5%
    }

    public function test_admin_can_download_all_pdf_and_individual_tenant_pdf(): void
    {
        $owner = User::create([
            'name' => 'Pak Budi',
            'username' => 'tenda-b01',
            'email' => 'tenda-b01@tenant.local',
            'role' => 'user',
            'password' => bcrypt('secret'),
        ]);

        $store = Store::create([
            'event_id' => $this->event->id,
            'owner_id' => $owner->id,
            'name' => 'Warung Mie Ayam',
            'booth_number' => 'B-01',
            'access_uuid' => 'test-uuid-1234',
            'is_active' => true,
        ]);

        // 1. Download overall event PDF (All)
        $allPdfResponse = $this->actingAs($this->admin)->get(route('admin.laporan.pdf'));
        $allPdfResponse->assertOk();
        $this->assertEquals('application/pdf', $allPdfResponse->headers->get('Content-Type'));

        // 2. Download specific tenant PDF (Single Tenant)
        $tenantPdfResponse = $this->actingAs($this->admin)->get(route('admin.laporan.pdf', ['store_id' => $store->id]));
        $tenantPdfResponse->assertOk();
        $this->assertEquals('application/pdf', $tenantPdfResponse->headers->get('Content-Type'));
    }

    public function test_admin_can_reject_cash_verification(): void
    {
        $owner = User::create([
            'name' => 'Pak Budi',
            'username' => 'tenda-b02',
            'email' => 'tenda-b02@tenant.local',
            'role' => 'user',
            'password' => bcrypt('secret'),
        ]);

        $store = Store::create([
            'event_id' => $this->event->id,
            'owner_id' => $owner->id,
            'name' => 'Warung Nasi',
            'booth_number' => 'B-02',
            'access_uuid' => 'test-uuid-5678',
            'is_active' => true,
        ]);

        $tx = Transaction::create([
            'invoice_code' => 'INV-TEST-REJECT-001',
            'store_id' => $store->id,
            'cashier_id' => $owner->id,
            'total_amount' => 25000,
            'payment_method' => 'cash',
            'amount_paid' => 30000,
            'change_due' => 5000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.verifikasi-cash.reject', $tx), [
            'reason' => 'Testing / Uji coba transaksi',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $tx->refresh();
        $this->assertEquals('rejected', $tx->status);
        $this->assertEquals('Testing / Uji coba transaksi', $tx->rejection_reason);
        $this->assertEquals($this->admin->id, $tx->verified_by);
    }

    public function test_admin_can_delete_pending_cash_transaction_permanently(): void
    {
        $owner = User::create([
            'name' => 'Pak Budi',
            'username' => 'tenda-b03',
            'email' => 'tenda-b03@tenant.local',
            'role' => 'user',
            'password' => bcrypt('secret'),
        ]);

        $store = Store::create([
            'event_id' => $this->event->id,
            'owner_id' => $owner->id,
            'name' => 'Warung Kopi',
            'booth_number' => 'B-03',
            'access_uuid' => 'test-uuid-9999',
            'is_active' => true,
        ]);

        $tx = Transaction::create([
            'invoice_code' => 'INV-TEST-DELETE-001',
            'store_id' => $store->id,
            'cashier_id' => $owner->id,
            'total_amount' => 10000,
            'payment_method' => 'cash',
            'amount_paid' => 10000,
            'change_due' => 0,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->deleteJson(route('admin.verifikasi-cash.destroy', $tx));

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('transactions', ['id' => $tx->id]);
    }
}
