<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use App\Services\RevenueSplitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevenueSplitServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RevenueSplitService $service;
    protected Store $store;
    protected User $cashier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RevenueSplitService();

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
    }

    public function test_standard_nominal_revenue_split(): void
    {
        $tx = Transaction::create([
            'invoice_code' => 'INV-TEST-001',
            'store_id' => $this->store->id,
            'cashier_id' => $this->cashier->id,
            'total_amount' => 40000.00,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        $split = $this->service->calculate($tx);

        // 75% Owner = 30.000, 22.5% Admin = 9.000, 2.5% Superadmin/Platform = 1.000
        $this->assertEquals(30000.00, (float) $split->owner_share);
        $this->assertEquals(9000.00, (float) $split->admin_gross_share);
        $this->assertEquals(1000.00, (float) $split->superadmin_share);
        $this->assertEquals(9000.00, (float) $split->admin_net_share);
    }

    public function test_small_nominal_revenue_split(): void
    {
        $tx = Transaction::create([
            'invoice_code' => 'INV-TEST-002',
            'store_id' => $this->store->id,
            'cashier_id' => $this->cashier->id,
            'total_amount' => 10000.00,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        $split = $this->service->calculate($tx);

        // 75% Owner = 7.500, 22.5% Admin = 2.250, 2.5% Superadmin/Platform = 250
        $this->assertEquals(7500.00, (float) $split->owner_share);
        $this->assertEquals(2250.00, (float) $split->admin_gross_share);
        $this->assertEquals(250.00, (float) $split->superadmin_share);
        $this->assertEquals(2250.00, (float) $split->admin_net_share);
    }
}
