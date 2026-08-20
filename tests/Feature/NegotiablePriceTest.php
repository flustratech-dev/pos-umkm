<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\RevenueSplitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class NegotiablePriceTest extends TestCase
{
    use RefreshDatabase;

    protected CheckoutService $checkout;
    protected User $tenantUser;
    protected Store $store;
    protected Product $negotiable;
    protected Product $fixed;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checkout = new CheckoutService(new RevenueSplitService());

        $event = Event::create([
            'name' => 'Bazar Nego',
            'slug' => 'bazar-nego',
            'is_active' => true,
        ]);

        $this->tenantUser = User::create([
            'name' => 'Pemilik Stand',
            'username' => 'stand_nego',
            'email' => 'stand_nego@example.com',
            'role' => 'user',
            'password' => bcrypt('password'),
        ]);

        $this->store = Store::create([
            'event_id' => $event->id,
            'owner_id' => $this->tenantUser->id,
            'name' => 'Stand Nego',
            'booth_number' => 'N019',
            'is_active' => true,
        ]);

        $this->tenantUser->update(['store_id' => $this->store->id]);

        $this->negotiable = Product::create([
            'store_id' => $this->store->id,
            'title' => 'Kaos Event',
            'price' => 100000,
            'is_negotiable' => true,
            'min_price' => 70000,
            'max_price' => 100000,
            'category' => 'Merchandise',
            'is_active' => true,
        ]);

        $this->fixed = Product::create([
            'store_id' => $this->store->id,
            'title' => 'Es Teh',
            'price' => 5000,
            'category' => 'Minuman',
            'is_active' => true,
        ]);
    }

    public function test_user_can_create_product_with_price_range(): void
    {
        $this->actingAs($this->tenantUser)->postJson(route('user.produk.store'), [
            'title' => 'Totebag Bazar',
            'is_negotiable' => true,
            'min_price' => 40000,
            'max_price' => 65000,
            'category' => 'Merchandise',
        ])->assertOk()->assertJson(['success' => true]);

        $product = Product::where('title', 'Totebag Bazar')->first();

        $this->assertTrue($product->is_negotiable);
        $this->assertEquals(40000, (float) $product->min_price);
        $this->assertEquals(65000, (float) $product->max_price);
        // Harga tertinggi jadi harga acuan yang tampil di katalog.
        $this->assertEquals(65000, (float) $product->price);
    }

    public function test_price_range_is_validated(): void
    {
        $this->actingAs($this->tenantUser)->postJson(route('user.produk.store'), [
            'title' => 'Range Terbalik',
            'is_negotiable' => true,
            'min_price' => 90000,
            'max_price' => 50000,
            'category' => 'Merchandise',
        ])->assertStatus(422)->assertJsonValidationErrors('max_price');

        $this->actingAs($this->tenantUser)->postJson(route('user.produk.store'), [
            'title' => 'Tanpa Rentang',
            'is_negotiable' => true,
            'category' => 'Merchandise',
        ])->assertStatus(422)->assertJsonValidationErrors(['min_price', 'max_price']);
    }

    public function test_turning_off_negotiable_clears_the_range(): void
    {
        $this->actingAs($this->tenantUser)->putJson(route('user.produk.update', $this->negotiable), [
            'title' => 'Kaos Event',
            'price' => 95000,
            'is_negotiable' => false,
            'category' => 'Merchandise',
        ])->assertOk();

        $this->negotiable->refresh();

        $this->assertFalse($this->negotiable->is_negotiable);
        $this->assertNull($this->negotiable->min_price);
        $this->assertNull($this->negotiable->max_price);
        $this->assertEquals(95000, (float) $this->negotiable->price);
    }

    public function test_checkout_accepts_a_price_inside_the_range(): void
    {
        $tx = $this->checkout->processCashCheckout($this->store, $this->tenantUser, [
            ['product_id' => $this->negotiable->id, 'qty' => 2, 'price' => 85000],
        ], 200000);

        $this->assertEquals(170000, (float) $tx->total_amount);

        $item = $tx->items->first();
        $this->assertEquals(85000, (float) $item->price);
        $this->assertEquals(100000, (float) $item->original_price);
        $this->assertTrue($item->is_negotiated);
    }

    public function test_checkout_rejects_a_price_below_the_range(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->checkout->processCashCheckout($this->store, $this->tenantUser, [
            ['product_id' => $this->negotiable->id, 'qty' => 1, 'price' => 50000],
        ], 100000);
    }

    public function test_checkout_rejects_a_price_above_the_range(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->checkout->processCashCheckout($this->store, $this->tenantUser, [
            ['product_id' => $this->negotiable->id, 'qty' => 1, 'price' => 150000],
        ], 200000);
    }

    public function test_manual_price_is_ignored_for_fixed_price_products(): void
    {
        $tx = $this->checkout->processCashCheckout($this->store, $this->tenantUser, [
            ['product_id' => $this->fixed->id, 'qty' => 1, 'price' => 1],
        ], 10000);

        $item = $tx->items->first();

        $this->assertEquals(5000, (float) $item->price);
        $this->assertEquals(5000, (float) $tx->total_amount);
        $this->assertFalse($item->is_negotiated);
    }

    public function test_checkout_without_price_falls_back_to_the_list_price(): void
    {
        $tx = $this->checkout->processCashCheckout($this->store, $this->tenantUser, [
            ['product_id' => $this->negotiable->id, 'qty' => 1],
        ], 100000);

        $this->assertEquals(100000, (float) $tx->items->first()->price);
    }

    public function test_negotiated_price_flows_into_the_cashier_endpoint(): void
    {
        $response = $this->actingAs($this->tenantUser)->postJson(route('user.kasir.checkout-cash'), [
            'items' => [
                ['product_id' => $this->negotiable->id, 'qty' => 1, 'price' => 75000],
            ],
            'amount_paid' => 75000,
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('transaction_items', [
            'product_id' => $this->negotiable->id,
            'price' => 75000.00,
            'original_price' => 100000.00,
        ]);
    }

    public function test_out_of_range_price_is_rejected_by_the_cashier_endpoint(): void
    {
        $this->actingAs($this->tenantUser)->postJson(route('user.kasir.checkout-cash'), [
            'items' => [
                ['product_id' => $this->negotiable->id, 'qty' => 1, 'price' => 10000],
            ],
            'amount_paid' => 10000,
        ])->assertStatus(422)->assertJson(['success' => false]);

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_qris_checkout_keeps_the_negotiated_total(): void
    {
        Storage::fake('public');

        $tx = $this->checkout->processQrisCheckout($this->store, $this->tenantUser, [
            ['product_id' => $this->negotiable->id, 'qty' => 1, 'price' => 80000],
        ], UploadedFile::fake()->image('bukti.jpg'));

        // Harga nego + kode unik yang mengikuti kode tenda (N019 -> 19)
        $this->assertEquals(80019, (float) $tx->total_amount);
        $this->assertEquals(80000, (float) $tx->items->first()->price);
    }
}
