<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Product;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrisWithoutProofTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $tenantUser;
    protected Store $store;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin EO',
            'username' => 'admin_darurat',
            'email' => 'admin_darurat@example.com',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $event = Event::create([
            'name' => 'Bazar Darurat',
            'slug' => 'bazar-darurat',
            'is_active' => true,
        ]);

        $this->tenantUser = User::create([
            'name' => 'Pemilik Stand',
            'username' => 'stand_darurat',
            'email' => 'stand_darurat@example.com',
            'role' => 'user',
            'password' => bcrypt('password'),
        ]);

        $this->store = Store::create([
            'event_id' => $event->id,
            'owner_id' => $this->tenantUser->id,
            'name' => 'Warung Darurat',
            'booth_number' => '019',
            'is_active' => true,
        ]);

        $this->tenantUser->update(['store_id' => $this->store->id]);

        $this->product = Product::create([
            'store_id' => $this->store->id,
            'title' => 'Nasi Goreng',
            'price' => 10000,
            'category' => 'Makanan',
            'is_active' => true,
        ]);
    }

    protected function save(array $overrides = [])
    {
        return $this->actingAs($this->tenantUser)->postJson(
            route('user.kasir.checkout-qris-without-proof'),
            array_merge([
                'items' => [['product_id' => $this->product->id, 'qty' => 1]],
                'reason' => 'Ukuran file terlalu besar untuk server.',
            ], $overrides)
        );
    }

    public function test_transaction_is_recorded_as_paid_without_any_approval(): void
    {
        // Uangnya sudah masuk rekening, yang kurang cuma catatannya di sistem.
        $this->save()->assertOk()->assertJson(['success' => true]);

        $transaction = Transaction::firstOrFail();

        $this->assertEquals('paid', $transaction->status);
        $this->assertEquals('qris', $transaction->payment_method);
        $this->assertNotNull($transaction->paid_at);
        $this->assertNull($transaction->paymentProof);
        $this->assertNull($transaction->verified_by);
    }

    public function test_total_still_includes_the_booth_unique_code(): void
    {
        $this->save()->assertOk();

        $this->assertEquals(10019, (float) Transaction::firstOrFail()->total_amount);
    }

    public function test_revenue_split_is_generated_right_away(): void
    {
        $this->save()->assertOk();

        $transaction = Transaction::with('revenueSplit')->firstOrFail();

        $this->assertNotNull($transaction->revenueSplit, 'Bagi hasil harus langsung dihitung seperti QRIS biasa.');
        $this->assertEquals(10019 * 0.75, (float) $transaction->revenueSplit->owner_share);
    }

    public function test_reason_is_stored_and_flagged_for_the_organiser(): void
    {
        $this->save(['reason' => 'Ukuran file terlalu besar untuk server.'])->assertOk();

        $transaction = Transaction::firstOrFail();

        $this->assertEquals('Ukuran file terlalu besar untuk server.', $transaction->proof_failure_reason);
        $this->assertTrue($transaction->is_proof_missing);
    }

    public function test_reason_is_required(): void
    {
        $this->save(['reason' => ''])->assertStatus(422)->assertJsonValidationErrors('reason');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_negotiated_prices_still_apply(): void
    {
        $merch = Product::create([
            'store_id' => $this->store->id,
            'title' => 'Kaos Event',
            'price' => 100000,
            'is_negotiable' => true,
            'min_price' => 70000,
            'max_price' => 100000,
            'category' => 'Merchandise',
            'is_active' => true,
        ]);

        $this->save([
            'items' => [['product_id' => $merch->id, 'qty' => 1, 'price' => 85000]],
        ])->assertOk();

        $this->assertEquals(85019, (float) Transaction::firstOrFail()->total_amount);
    }

    public function test_out_of_range_negotiated_price_is_still_rejected(): void
    {
        $merch = Product::create([
            'store_id' => $this->store->id,
            'title' => 'Kaos Event',
            'price' => 100000,
            'is_negotiable' => true,
            'min_price' => 70000,
            'max_price' => 100000,
            'category' => 'Merchandise',
            'is_active' => true,
        ]);

        $this->save([
            'items' => [['product_id' => $merch->id, 'qty' => 1, 'price' => 10000]],
        ])->assertStatus(422);

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_transaction_shows_up_in_the_reports(): void
    {
        $this->save()->assertOk();

        // Laporan warung
        $this->actingAs($this->tenantUser)->get(route('user.laporan'))
            ->assertOk()
            ->assertSee('"is_proof_missing":true', false);

        // Laporan EO
        $this->actingAs($this->admin)->get(route('admin.laporan'))
            ->assertOk()
            ->assertSee('Tanpa Bukti');
    }

    public function test_normal_qris_transactions_are_not_flagged(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $this->actingAs($this->tenantUser)->post(route('user.kasir.checkout-qris'), [
            'items' => [['product_id' => $this->product->id, 'qty' => 1]],
            'proof_image' => \Illuminate\Http\UploadedFile::fake()->image('bukti.jpg'),
        ], ['Accept' => 'application/json'])->assertOk();

        $transaction = Transaction::firstOrFail();

        $this->assertFalse($transaction->is_proof_missing);
        $this->assertNull($transaction->proof_failure_reason);
        $this->assertNotNull($transaction->paymentProof);
    }
}
