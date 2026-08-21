<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use App\Services\RevenueSplitService;
use App\Services\TransactionVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected TransactionVerificationService $service;
    protected Store $store;
    protected User $admin;
    protected User $cashier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TransactionVerificationService(new RevenueSplitService());

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

    public function test_approve_qris_transaction(): void
    {
        $tx = Transaction::create([
            'invoice_code' => 'INV-TEST-QRIS-01',
            'store_id' => $this->store->id,
            'cashier_id' => $this->cashier->id,
            'total_amount' => 50000.00,
            'payment_method' => 'qris',
            'status' => 'pending_verification',
        ]);

        $approvedTx = $this->service->approve($tx, $this->admin);

        $this->assertEquals('paid', $approvedTx->status);
        $this->assertEquals($this->admin->id, $approvedTx->verified_by);
        $this->assertNotNull($approvedTx->verified_at);
        $this->assertNotNull($approvedTx->revenueSplit);
        $this->assertEquals(37500.00, (float) $approvedTx->revenueSplit->owner_share);
    }

    public function test_reject_qris_transaction(): void
    {
        $tx = Transaction::create([
            'invoice_code' => 'INV-TEST-QRIS-02',
            'store_id' => $this->store->id,
            'cashier_id' => $this->cashier->id,
            'total_amount' => 50000.00,
            'payment_method' => 'qris',
            'status' => 'pending_verification',
        ]);

        $rejectedTx = $this->service->reject($tx, $this->admin, 'Bukti transfer buram/tidak terbaca');

        $this->assertEquals('rejected', $rejectedTx->status);
        $this->assertEquals('Bukti transfer buram/tidak terbaca', $rejectedTx->rejection_reason);
        $this->assertNull($rejectedTx->revenueSplit);
    }

    public function test_complete_cash_transaction_without_payment(): void
    {
        $tx = Transaction::create([
            'invoice_code' => 'INV-TEST-CASH-NOPAY',
            'store_id' => $this->store->id,
            'cashier_id' => $this->cashier->id,
            'total_amount' => 75000.00,
            'payment_method' => 'cash',
            'status' => 'pending',
        ]);

        $completedTx = $this->service->completeWithoutPayment($tx, $this->admin, 'Tanpa Pembayaran');

        $this->assertEquals('rejected', $completedTx->status);
        $this->assertEquals('Tanpa Pembayaran', $completedTx->rejection_reason);
        $this->assertEquals($this->admin->id, $completedTx->verified_by);
        $this->assertTrue($completedTx->is_without_payment);
        $this->assertNull($completedTx->revenueSplit);
    }
}
