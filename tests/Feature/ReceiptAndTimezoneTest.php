<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Product;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReceiptAndTimezoneTest extends TestCase
{
    use RefreshDatabase;

    protected User $tenantUser;
    protected Store $store;
    protected Product $product;
    protected Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        $event = Event::create([
            'name' => 'Bazar Waktu',
            'slug' => 'bazar-waktu',
            'is_active' => true,
        ]);

        $this->tenantUser = User::create([
            'name' => 'Pemilik Stand',
            'username' => 'stand_waktu',
            'email' => 'stand_waktu@example.com',
            'role' => 'user',
            'password' => bcrypt('password'),
        ]);

        $this->store = Store::create([
            'event_id' => $event->id,
            'owner_id' => $this->tenantUser->id,
            'name' => 'Aldi Burger',
            'booth_number' => '018',
            'is_active' => true,
        ]);

        $this->tenantUser->update(['store_id' => $this->store->id]);

        $this->product = Product::create([
            'store_id' => $this->store->id,
            'title' => 'Gallagher',
            'price' => 77000,
            'category' => 'Makanan',
            'is_active' => true,
        ]);

        $this->transaction = Transaction::create([
            'invoice_code' => 'INV-20260819-7EKI',
            'store_id' => $this->store->id,
            'cashier_id' => $this->tenantUser->id,
            'total_amount' => 77000,
            'payment_method' => 'cash',
            'amount_paid' => 100000,
            'change_due' => 23000,
            'status' => 'paid',
            'paid_at' => Carbon::parse('2026-08-19 09:13:00', 'Asia/Jakarta'),
        ]);

        TransactionItem::create([
            'transaction_id' => $this->transaction->id,
            'product_id' => $this->product->id,
            'title' => 'Gallagher',
            'price' => 77000,
            'qty' => 1,
            'subtotal' => 77000,
        ]);
    }

    public function test_application_runs_on_jakarta_time(): void
    {
        $this->assertEquals('Asia/Jakarta', config('app.timezone'));
        $this->assertEquals('WIB', now()->format('T'));
    }

    public function test_new_transactions_are_stamped_with_jakarta_wall_clock(): void
    {
        // Ini yang dulu bikin kartu verifikasi cash menampilkan 02:13
        // padahal jam di lokasi acara 09:13.
        $jakartaNow = Carbon::now('Asia/Jakarta');

        $transaction = Transaction::create([
            'invoice_code' => 'INV-BARU',
            'store_id' => $this->store->id,
            'cashier_id' => $this->tenantUser->id,
            'total_amount' => 10000,
            'payment_method' => 'cash',
            'status' => 'pending',
        ]);

        $stored = Carbon::parse(DB::table('transactions')->where('id', $transaction->id)->value('created_at'));

        $this->assertEquals($jakartaNow->format('Y-m-d H:i'), $stored->format('Y-m-d H:i'));
    }

    public function test_invoice_code_uses_the_jakarta_date(): void
    {
        $response = $this->actingAs($this->tenantUser)->postJson(route('user.kasir.checkout-cash'), [
            'items' => [['product_id' => $this->product->id, 'qty' => 1]],
            'amount_paid' => 77000,
        ])->assertOk();

        $invoice = $response->json('transaction.invoice_code');

        $this->assertStringContainsString(Carbon::now('Asia/Jakarta')->format('Ymd'), $invoice);
    }

    public function test_printed_receipt_shows_the_transaction_time(): void
    {
        $this->get(route('receipt.print', $this->transaction))
            ->assertOk()
            ->assertSee('19/08/26 09:13');
    }

    public function test_receipt_does_not_expose_the_revenue_split(): void
    {
        // Struk dilihat pembeli, jadi porsi EO dan porsi warung tidak boleh tampil.
        $html = $this->actingAs($this->tenantUser)->get(route('user.kasir'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('Porsi EO', $html);
        $this->assertStringNotContainsString('Porsi Warung', $html);

        $this->get(route('receipt.print', $this->transaction))
            ->assertOk()
            ->assertDontSee('Porsi EO')
            ->assertDontSee('Hak Bersih Warung');
    }

    public function test_tenant_report_still_shows_the_revenue_split(): void
    {
        // Bagi hasil tetap terlihat di laporan pemilik stand, hanya struk yang bersih.
        $this->actingAs($this->tenantUser)->get(route('user.laporan'))
            ->assertOk()
            ->assertSee('75%', false);
    }
}
