<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $tenantUser;
    protected Event $event;
    protected Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin EO',
            'username' => 'admin_kategori',
            'email' => 'admin_kategori@example.com',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $this->event = Event::create([
            'name' => 'Bazar Kategori',
            'slug' => 'bazar-kategori',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        $this->tenantUser = User::create([
            'name' => 'Pemilik Stand',
            'username' => 'stand_merch',
            'email' => 'stand_merch@example.com',
            'role' => 'user',
            'password' => bcrypt('password'),
        ]);

        $this->store = Store::create([
            'event_id' => $this->event->id,
            'owner_id' => $this->tenantUser->id,
            'name' => 'Stand Merch',
            'booth_number' => 'M01',
            'category' => 'merchandise',
            'is_active' => true,
        ]);

        $this->tenantUser->update(['store_id' => $this->store->id]);
    }

    public function test_merchandise_is_registered_as_product_category(): void
    {
        $this->assertArrayHasKey('Merchandise', Product::CATEGORIES);
    }

    public function test_user_can_create_merchandise_product(): void
    {
        $response = $this->actingAs($this->tenantUser)->postJson(route('user.produk.store'), [
            'title' => 'Kaos Event Official',
            'price' => 85000,
            'category' => 'Merchandise',
            'stock_badge' => 'Tersedia',
            'is_active' => true,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'product' => ['category' => 'Merchandise'],
            ]);

        $this->assertDatabaseHas('products', [
            'store_id' => $this->store->id,
            'title' => 'Kaos Event Official',
            'category' => 'Merchandise',
        ]);
    }

    public function test_user_can_change_product_category_to_merchandise(): void
    {
        $product = Product::create([
            'store_id' => $this->store->id,
            'title' => 'Tumbler Edisi Terbatas',
            'price' => 60000,
            'category' => 'Snack',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->tenantUser)->putJson(route('user.produk.update', $product), [
            'title' => 'Tumbler Edisi Terbatas',
            'price' => 60000,
            'category' => 'Merchandise',
            'is_active' => true,
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertEquals('Merchandise', $product->fresh()->category);
    }

    public function test_merchandise_filter_is_rendered_on_user_pages(): void
    {
        Product::create([
            'store_id' => $this->store->id,
            'title' => 'Totebag Bazar',
            'price' => 45000,
            'category' => 'Merchandise',
            'is_active' => true,
        ]);

        $this->actingAs($this->tenantUser)->get(route('user.produk'))
            ->assertOk()
            ->assertSee('Merchandise')
            ->assertSee('Totebag Bazar');

        $this->actingAs($this->tenantUser)->get(route('user.kasir'))
            ->assertOk()
            ->assertSee('Merchandise');
    }

    public function test_merchandise_filter_is_rendered_on_admin_product_page(): void
    {
        Product::create([
            'store_id' => $this->store->id,
            'title' => 'Gantungan Kunci',
            'price' => 15000,
            'category' => 'Merchandise',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)->get(route('admin.produk'))
            ->assertOk()
            ->assertSee('Merchandise');
    }
}
