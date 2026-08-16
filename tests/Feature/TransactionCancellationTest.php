<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use App\Services\RevenueSplitService;
use App\Services\TransactionCancellationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class TransactionCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected TransactionCancellationService $service;
    protected Store $store;
    protected User $admin;
    protected User $cashier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TransactionCancellationService();

        $event = Event::create([
            'name' => 'Event Test',
            'slug' => 'event-test',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Admin EO',
            'username' => 'admin.eo',
            'email' => 'admin@test.com',
            'role' => 'admin',
            'password' => bcrypt('password'),
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
    }

    public function test_cancel_paid_transaction_success(): void
    {
        $tx = Transaction::create([
            'invoice_code' => 'INV-TEST-CANCEL-01',
            'store_id' => $this->store->id,
            'cashier_id' => $this->cashier->id,
            'total_amount' => 50000.00,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        (new RevenueSplitService())->calculate($tx);

        $cancelledTx = $this->service->cancel(
            $tx,
            $this->admin,
            'Salah input barang/harga',
            'Salah ketik menu ayam',
            true
        );

        $this->assertEquals('cancelled', $cancelledTx->status);
        $this->assertTrue($cancelledTx->refund_ack_confirmed);
        $this->assertEquals($this->admin->id, $cancelledTx->cancelled_by);
        $this->assertStringContainsString('Salah input barang/harga', $cancelledTx->cancellation_reason);
    }

    public function test_cancel_paid_transaction_fails_without_checkbox(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $tx = Transaction::create([
            'invoice_code' => 'INV-TEST-CANCEL-02',
            'store_id' => $this->store->id,
            'cashier_id' => $this->cashier->id,
            'total_amount' => 50000.00,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        $this->service->cancel(
            $tx,
            $this->admin,
            'Salah input barang/harga',
            null,
            false // Not confirmed
        );
    }
}
