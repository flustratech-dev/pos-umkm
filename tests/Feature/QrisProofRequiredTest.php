<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Product;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QrisProofRequiredTest extends TestCase
{
    use RefreshDatabase;

    protected User $tenantUser;
    protected Store $store;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $event = Event::create([
            'name' => 'Bazar Bukti',
            'slug' => 'bazar-bukti',
            'is_active' => true,
        ]);

        $this->tenantUser = User::create([
            'name' => 'Pemilik Stand',
            'username' => 'stand_bukti',
            'email' => 'stand_bukti@example.com',
            'role' => 'user',
            'password' => bcrypt('password'),
        ]);

        $this->store = Store::create([
            'event_id' => $event->id,
            'owner_id' => $this->tenantUser->id,
            'name' => 'Warung Bukti',
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

    protected function items(): array
    {
        return [['product_id' => $this->product->id, 'qty' => 1]];
    }

    public function test_qris_checkout_is_rejected_without_a_proof_image(): void
    {
        $this->actingAs($this->tenantUser)
            ->postJson(route('user.kasir.checkout-qris'), ['items' => $this->items()])
            ->assertStatus(422)
            ->assertJsonValidationErrors('proof_image');

        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseCount('payment_proofs', 0);
    }

    public function test_qris_checkout_is_rejected_when_the_proof_is_not_an_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->tenantUser)
            ->post(route('user.kasir.checkout-qris'), [
                'items' => $this->items(),
                'proof_image' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
            ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('proof_image');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_qris_checkout_succeeds_with_a_proof_image(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->tenantUser)
            ->post(route('user.kasir.checkout-qris'), [
                'items' => $this->items(),
                'proof_image' => UploadedFile::fake()->image('bukti.jpg'),
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $transaction = Transaction::firstOrFail();

        $this->assertEquals('paid', $transaction->status);
        $this->assertNotNull($transaction->paymentProof);
        Storage::disk('public')->assertExists($transaction->paymentProof->proof_path);

        $this->assertEquals(10019, (float) $response->json('transaction.total_amount'));
    }

    public function test_every_paid_qris_transaction_has_an_archived_proof(): void
    {
        Storage::fake('public');

        $this->actingAs($this->tenantUser)
            ->post(route('user.kasir.checkout-qris'), [
                'items' => $this->items(),
                'proof_image' => UploadedFile::fake()->image('bukti.jpg'),
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $qrisTransactions = Transaction::where('payment_method', 'qris')->with('paymentProof')->get();

        $this->assertCount(1, $qrisTransactions);
        $qrisTransactions->each(function (Transaction $tx) {
            $this->assertNotNull($tx->paymentProof, "Transaksi {$tx->invoice_code} lunas tanpa bukti transfer.");
        });
    }

    public function test_proof_compressed_to_webp_by_the_browser_is_accepted(): void
    {
        Storage::fake('public');

        // Kasir mengunggah foto kamera; browser mengecilkannya jadi WebP dulu.
        $this->actingAs($this->tenantUser)
            ->post(route('user.kasir.checkout-qris'), [
                'items' => $this->items(),
                'proof_image' => UploadedFile::fake()->image('bukti.webp')->mimeType('image/webp'),
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $proofPath = Transaction::firstOrFail()->paymentProof->proof_path;

        Storage::disk('public')->assertExists($proofPath);
        $this->assertStringEndsWith('.webp', $proofPath);
    }

    public function test_pay_button_stays_locked_until_a_proof_is_attached(): void
    {
        $html = $this->actingAs($this->tenantUser)->get(route('user.kasir'))
            ->assertOk()
            ->getContent();

        // Tombol bayar QRIS terkunci selama bukti belum diunggah.
        $this->assertStringContainsString(':disabled="!$store.app.qrisProofFile"', $html);
        $this->assertStringContainsString('Unggah bukti transfer dulu untuk mengaktifkan tombol bayar.', $html);
        $this->assertStringNotContainsString('(Opsional / Arsip Laporan)', $html);
    }
}
