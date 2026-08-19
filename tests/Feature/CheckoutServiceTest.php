<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\RevenueSplitService;
use App\Services\TransactionVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CheckoutService $service;
    protected Store $store;
    protected User $cashier;
    protected Product $product1;
    protected Product $product2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CheckoutService(new RevenueSplitService());

        $event = Event::create([
            'name' => 'Event Test',
            'slug' => 'event-test',
            'is_active' => true,
        ]);

        $this->cashier = User::create([
            'name' => 'Cashier Test',
            'username' => 'cashier.test',
            'email' => 'cashier@test.com',
            'role' => 'user',
            'password' => bcrypt('password'),
        ]);

        $this->store = Store::create([
            'event_id' => $event->id,
            'owner_id' => $this->cashier->id,
            'name' => 'Store Test',
            'is_active' => true,
        ]);

        $this->product1 = Product::create([
            'store_id' => $this->store->id,
            'title' => 'Nasi Goreng',
            'price' => 20000.00,
            'is_active' => true,
        ]);

        $this->product2 = Product::create([
            'store_id' => $this->store->id,
            'title' => 'Es Teh',
            'price' => 5000.00,
            'is_active' => true,
        ]);
    }

    public function test_cash_checkout_success(): void
    {
        $items = [
            ['product_id' => $this->product1->id, 'qty' => 2], // 40.000
            ['product_id' => $this->product2->id, 'qty' => 1], // 5.000 -> Total 45.000
        ];

        $tx = $this->service->processCashCheckout($this->store, $this->cashier, $items, 50000.00);

        // Cash checkout now creates 'pending' status awaiting admin confirmation
        $this->assertEquals('pending', $tx->status);
        $this->assertEquals(45000.00, (float) $tx->total_amount);
        $this->assertEquals(50000.00, (float) $tx->amount_paid);
        $this->assertEquals(5000.00, (float) $tx->change_due);
        $this->assertNull($tx->revenueSplit); // Revenue split not generated until admin confirms
        $this->assertCount(2, $tx->items);

        // When admin confirms cash payment at exit cashier
        $admin = User::create([
            'name' => 'Admin EO',
            'username' => 'admin.eo',
            'email' => 'admin@eo.com',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $verificationService = new TransactionVerificationService(new RevenueSplitService());
        $confirmedTx = $verificationService->confirmCash($tx, $admin);

        $this->assertEquals('paid', $confirmedTx->status);
        $this->assertNotNull($confirmedTx->revenueSplit);
        $this->assertEquals(33750.00, (float) $confirmedTx->revenueSplit->owner_share); // 75%
        $this->assertEquals(10125.00, (float) $confirmedTx->revenueSplit->admin_gross_share); // 22.5%
        $this->assertEquals(1125.00, (float) $confirmedTx->revenueSplit->superadmin_share); // 2.5%
    }

    public function test_cash_checkout_fails_on_underpaid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $items = [
            ['product_id' => $this->product1->id, 'qty' => 1], // 20.000
        ];

        $this->service->processCashCheckout($this->store, $this->cashier, $items, 15000.00);
    }

    public function test_qris_checkout_success(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('proof.jpg');

        $items = [
            ['product_id' => $this->product1->id, 'qty' => 1], // 20.000
        ];

        $tx = $this->service->processQrisCheckout($this->store, $this->cashier, $items, $file);

        $this->assertEquals('paid', $tx->status);
        // Stand ini tidak punya kode tenda, jadi kode uniknya jatuh ke id stand.
        $this->assertEquals(20000.00 + $this->store->unique_code, (float) $tx->total_amount);
        $this->assertNotNull($tx->paymentProof);
        $this->assertNotNull($tx->revenueSplit);
    }
}
