<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductResponseTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $tenantUser;
    protected Store $store;
    protected Store $otherStore;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin EO',
            'username' => 'admin_produk',
            'email' => 'admin_produk@example.com',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $event = Event::create([
            'name' => 'Bazar Produk',
            'slug' => 'bazar-produk',
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        $this->tenantUser = User::create([
            'name' => 'Pemilik Stand',
            'username' => 'stand_produk',
            'email' => 'stand_produk@example.com',
            'role' => 'user',
            'password' => bcrypt('password'),
        ]);

        $this->store = Store::create([
            'event_id' => $event->id,
            'owner_id' => $this->tenantUser->id,
            'name' => 'Warung Berkah',
            'booth_number' => 'A01',
            'is_active' => true,
        ]);

        $this->tenantUser->update(['store_id' => $this->store->id]);

        $this->otherStore = Store::create([
            'event_id' => $event->id,
            'owner_id' => $this->admin->id,
            'name' => 'Warung Pindahan',
            'booth_number' => 'A02',
            'is_active' => true,
        ]);
    }

    public function test_created_product_response_carries_its_store(): void
    {
        // Kartu produk admin membaca product.store.name; tanpa relasi ini kartunya
        // menampilkan "Tanpa Warung" sampai halaman di-refresh.
        $this->actingAs($this->admin)->postJson(route('admin.produk.store'), [
            'store_id' => $this->store->id,
            'title' => 'Kaos Event',
            'price' => 90000,
            'category' => 'Merchandise',
        ])->assertOk()
            ->assertJsonPath('product.store_id', $this->store->id)
            ->assertJsonPath('product.store.name', 'Warung Berkah');
    }

    public function test_updated_product_response_carries_its_new_store(): void
    {
        $product = Product::create([
            'store_id' => $this->store->id,
            'title' => 'Kaos Event',
            'price' => 90000,
            'category' => 'Merchandise',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)->putJson(route('admin.produk.update', $product), [
            'store_id' => $this->otherStore->id,
            'title' => 'Kaos Event',
            'price' => 90000,
            'category' => 'Merchandise',
        ])->assertOk()
            ->assertJsonPath('product.store_id', $this->otherStore->id)
            ->assertJsonPath('product.store.name', 'Warung Pindahan');
    }

    public function test_tenant_product_response_carries_its_store(): void
    {
        $this->actingAs($this->tenantUser)->postJson(route('user.produk.store'), [
            'title' => 'Es Teh',
            'price' => 5000,
            'category' => 'Minuman',
        ])->assertOk()
            ->assertJsonPath('product.store.name', 'Warung Berkah');
    }
}
