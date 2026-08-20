<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Product;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\TestingModeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TestingModeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $superAdmin;
    protected User $tenantUser;
    protected Event $event;
    protected Store $store;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin EO',
            'username' => 'admin_eo_test',
            'email' => 'admin_testing@example.com',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin_test',
            'email' => 'superadmin_testing@example.com',
            'role' => 'superadmin',
            'password' => bcrypt('password'),
        ]);

        $this->event = Event::create([
            'name' => 'Bazar UMKM Ramadhan',
            'slug' => 'bazar-umkm-ramadhan',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'location' => 'Alun-alun Kota',
            'is_active' => true,
            'is_testing_mode' => false,
            'created_by' => $this->admin->id,
        ]);

        $this->tenantUser = User::create([
            'name' => 'Pemilik Warung',
            'username' => 'warung_berkah',
            'email' => 'warung_testing@example.com',
            'role' => 'user',
            'password' => bcrypt('password'),
        ]);

        $this->store = Store::create([
            'name' => 'Warung Berkah',
            'owner_id' => $this->tenantUser->id,
            'event_id' => $this->event->id,
            'booth_number' => 'A01',
            'category' => 'Makanan',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'store_id' => $this->store->id,
            'title' => 'Nasi Goreng Spesial',
            'price' => 20000.00,
            'category' => 'Makanan',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_toggle_testing_mode_for_event(): void
    {
        $this->assertFalse($this->event->is_testing_mode);

        $response = $this->actingAs($this->admin)->postJson(route('admin.events.toggle-testing', $this->event), [
            'is_testing_mode' => true,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'is_testing_mode' => true,
            ]);

        $this->assertTrue($this->event->fresh()->is_testing_mode);

        // Toggle back off
        $responseOff = $this->actingAs($this->admin)->postJson(route('admin.events.toggle-testing', $this->event), [
            'is_testing_mode' => false,
        ]);

        $responseOff->assertOk()
            ->assertJson([
                'success' => true,
                'is_testing_mode' => false,
            ]);

        $this->assertFalse($this->event->fresh()->is_testing_mode);
    }

    public function test_transactions_created_during_testing_mode_are_marked_as_testing(): void
    {
        // 1. Enable testing mode
        $this->event->update(['is_testing_mode' => true]);

        $checkoutService = app(CheckoutService::class);

        // Cash checkout during testing
        $cashTx = $checkoutService->processCashCheckout(
            $this->store,
            $this->tenantUser,
            [['product_id' => $this->product->id, 'qty' => 1]],
            20000.00
        );

        $this->assertTrue($cashTx->is_testing);

        // QRIS checkout during testing
        Storage::fake('public');

        $qrisTx = $checkoutService->processQrisCheckout(
            $this->store,
            $this->tenantUser,
            [['product_id' => $this->product->id, 'qty' => 1]],
            UploadedFile::fake()->image('bukti.jpg')
        );

        $this->assertTrue($qrisTx->is_testing);
    }

    public function test_admin_can_reset_testing_transactions_and_keep_stores_and_products_intact(): void
    {
        Storage::fake('public');

        // 1. Enable testing mode
        $this->event->update(['is_testing_mode' => true]);

        $checkoutService = app(CheckoutService::class);
        $file = UploadedFile::fake()->image('test-proof.jpg');

        // Create 2 testing transactions
        $tx1 = $checkoutService->processCashCheckout(
            $this->store,
            $this->tenantUser,
            [['product_id' => $this->product->id, 'qty' => 2]],
            50000.00
        );

        $tx2 = $checkoutService->processQrisCheckout(
            $this->store,
            $this->tenantUser,
            [['product_id' => $this->product->id, 'qty' => 1]],
            $file
        );

        $this->assertEquals(2, Transaction::where('is_testing', true)->count());

        // 2. Perform reset
        $response = $this->actingAs($this->admin)->postJson(route('admin.events.reset-testing', $this->event));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'deleted_count' => 2,
            ]);

        // 3. Verify transactions are deleted
        $this->assertEquals(0, Transaction::where('is_testing', true)->count());
        $this->assertEquals(0, Transaction::count());

        // 4. Verify Store, Product, and Event remain completely intact
        $this->assertDatabaseHas('stores', ['id' => $this->store->id, 'name' => 'Warung Berkah']);
        $this->assertDatabaseHas('products', ['id' => $this->product->id, 'title' => 'Nasi Goreng Spesial']);
        $this->assertDatabaseHas('events', ['id' => $this->event->id, 'name' => 'Bazar UMKM Ramadhan']);
    }

    public function test_superadmin_can_toggle_and_reset_testing_mode(): void
    {
        // Superadmin toggles testing mode
        $response = $this->actingAs($this->superAdmin)->postJson(route('superadmin.events.toggle-testing', $this->event), [
            'is_testing_mode' => true,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'is_testing_mode' => true,
            ]);

        $this->assertTrue($this->event->fresh()->is_testing_mode);

        // Superadmin resets testing transactions
        $resetResponse = $this->actingAs($this->superAdmin)->postJson(route('superadmin.events.reset-testing', $this->event));

        $resetResponse->assertOk()
            ->assertJson([
                'success' => true,
            ]);
    }
}
