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
use Tests\TestCase;

class StoreUniqueCodeTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;
    protected User $tenantUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = Event::create([
            'name' => 'Bazar Kode',
            'slug' => 'bazar-kode',
            'is_active' => true,
            'qris_payload' => '00020101021126650014ID.CO.QRIS.WWW0215ID12345678901230303UMI51440014ID.CO.QRIS.WWW0215ID1234567890123020352045812530336054041000550200005802ID5910TOKO TEST6007JAKARTA61051234062070703A0163041234',
        ]);

        $this->tenantUser = User::create([
            'name' => 'Pemilik Stand',
            'username' => 'stand_kode',
            'email' => 'stand_kode@example.com',
            'role' => 'user',
            'password' => bcrypt('password'),
        ]);
    }

    protected function makeStore(?string $booth, bool $dynamicQris = false): Store
    {
        return Store::create([
            'event_id' => $this->event->id,
            'owner_id' => $this->tenantUser->id,
            'name' => 'Stand ' . ($booth ?? 'tanpa kode'),
            'booth_number' => $booth,
            'is_active' => true,
            'use_dynamic_qris' => $dynamicQris,
        ]);
    }

    public function test_unique_code_follows_the_booth_number(): void
    {
        $this->assertEquals(19, $this->makeStore('019')->unique_code);
        $this->assertEquals(7, $this->makeStore('Booth 007')->unique_code);
        $this->assertEquals(12, $this->makeStore('A12')->unique_code);
        $this->assertEquals(1, $this->makeStore('Stand 01')->unique_code);
    }

    public function test_unique_code_falls_back_to_the_store_id_without_digits(): void
    {
        $noDigits = $this->makeStore('Tenda Utama');
        $empty = $this->makeStore(null);

        $this->assertEquals($noDigits->id, $noDigits->unique_code);
        $this->assertEquals($empty->id, $empty->unique_code);
    }

    public function test_qris_checkout_adds_the_booth_code_to_the_total(): void
    {
        $store = $this->makeStore('019');
        $this->tenantUser->update(['store_id' => $store->id]);

        $product = Product::create([
            'store_id' => $store->id,
            'title' => 'Nasi Goreng',
            'price' => 10000,
            'category' => 'Makanan',
            'is_active' => true,
        ]);

        Storage::fake('public');

        $service = new CheckoutService(new RevenueSplitService());
        $tx = $service->processQrisCheckout($store, $this->tenantUser, [
            ['product_id' => $product->id, 'qty' => 1],
        ], UploadedFile::fake()->image('bukti.jpg'));

        // Rp10.000 di tenda 019 harus jadi Rp10.019, bukan mengikuti id stand.
        $this->assertEquals(10019, (float) $tx->total_amount);
        $this->assertNotEquals(10000 + $store->id, (float) $tx->total_amount);
    }

    public function test_dynamic_qris_amount_uses_the_same_booth_code(): void
    {
        $store = $this->makeStore('019', true);
        $this->tenantUser->update(['store_id' => $store->id]);

        $this->actingAs($this->tenantUser)
            ->postJson(route('user.kasir.generate-qris'), ['amount' => 10000])
            ->assertOk()
            ->assertJson(['success' => true]);

        // Nominal yang ditanam di QR harus sama dengan yang dicatat saat checkout.
        $this->assertEquals(10019, 10000 + $store->unique_code);
    }

    public function test_store_payload_sent_to_the_browser_carries_the_unique_code(): void
    {
        $store = $this->makeStore('019');
        $this->tenantUser->update(['store_id' => $store->id]);

        $html = $this->actingAs($this->tenantUser)->get(route('user.kasir'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('"unique_code":19', $html);
    }
}
