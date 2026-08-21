<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\RevenueSplit;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Event $event;
    protected Store $store;
    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin EO',
            'username' => 'admin_dash',
            'email' => 'admin_dash@example.com',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $this->event = Event::create([
            'name' => 'Youth Cup',
            'slug' => 'youth-cup',
            'is_active' => true,
        ]);

        $this->owner = User::create([
            'name' => 'Pemilik Stand',
            'username' => 'stand_dash',
            'email' => 'stand_dash@example.com',
            'role' => 'user',
            'password' => bcrypt('password'),
        ]);

        $this->store = Store::create([
            'event_id' => $this->event->id,
            'owner_id' => $this->owner->id,
            'name' => 'Warung Dash',
            'booth_number' => '001',
            'is_active' => true,
        ]);

        $this->owner->update(['store_id' => $this->store->id]);
    }

    protected function transaksi(string $metode, float $total, ?string $waktu = null, string $status = 'paid'): Transaction
    {
        $saat = $waktu ? Carbon::parse($waktu) : now();

        $tx = Transaction::create([
            'invoice_code' => 'INV-' . uniqid(),
            'store_id' => $this->store->id,
            'cashier_id' => $this->owner->id,
            'total_amount' => $total,
            'payment_method' => $metode,
            'amount_paid' => $total,
            'change_due' => 0,
            'status' => $status,
            'paid_at' => $status === 'paid' ? $saat : null,
        ]);

        Transaction::where('id', $tx->id)->update(['created_at' => $saat, 'updated_at' => $saat]);

        if ($status === 'paid') {
            RevenueSplit::create([
                'transaction_id' => $tx->id,
                'owner_share' => $total * 0.75,
                'admin_gross_share' => $total * 0.25,
                'superadmin_share' => $total * 0.025,
                'admin_net_share' => $total * 0.225,
                'calculated_at' => $saat,
            ]);
        }

        return $tx->fresh();
    }

    public function test_cards_count_every_transaction_not_only_the_recent_ones(): void
    {
        // Dashboard hanya mengirim 10 transaksi terakhir ke browser; kartunya
        // dulu dihitung dari 10 itu saja sehingga omzetnya jauh lebih kecil
        // daripada laporan.
        for ($i = 0; $i < 15; $i++) {
            $this->transaksi('cash', 10000, now()->subDays($i % 5)->toDateTimeString());
        }

        $html = $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()->getContent();

        // 15 transaksi x Rp10.000 = Rp150.000, bukan sekadar 10 terakhir.
        $this->assertStringContainsString('Rp 150.000', $html);
        $this->assertStringContainsString('>15</span> transaksi berhasil', $html);
    }

    public function test_cards_show_event_wide_totals(): void
    {
        $this->transaksi('cash', 100000);
        $this->transaksi('qris', 60000);
        $this->transaksi('cash', 25000, null, 'pending');

        $html = $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('Rp 160.000', $html);          // total omzet
        $this->assertStringContainsString('Rp 40.000', $html);           // bagian EO 25%
        $this->assertStringContainsString('>1</h3>', $html);             // warung terdaftar & pending cash
    }

    public function test_chart_series_come_from_the_server(): void
    {
        $this->transaksi('cash', 50000, now()->setTime(3, 15)->toDateTimeString());
        $this->transaksi('qris', 70000, now()->setTime(21, 40)->toDateTimeString());

        $html = $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('window.__SALES_TREND__', $html);
        $this->assertStringContainsString('window.__EVENT_STATS__', $html);

        preg_match('/window\.__SALES_TREND__ = (.*?);\n/s', $html, $m);
        $tren = json_decode($m[1], true);

        // Jam 3 pagi dan 9 malam harus ikut terhitung, bukan hanya jam kerja.
        $this->assertEquals(50000, $tren['1d']['cash'][3]);
        $this->assertEquals(70000, $tren['1d']['qris'][21]);
    }

    public function test_hourly_chart_covers_the_whole_day(): void
    {
        $tren = app(ReportService::class)->getSalesTrend($this->event);

        $this->assertCount(24, $tren['1d']['labels']);
        $this->assertEquals('00:00', $tren['1d']['labels'][0]);
        $this->assertEquals('23:00', $tren['1d']['labels'][23]);
    }

    public function test_daily_charts_cover_seven_and_thirty_days(): void
    {
        $this->transaksi('cash', 40000, now()->subDays(3)->toDateTimeString());

        $tren = app(ReportService::class)->getSalesTrend($this->event);

        $this->assertCount(7, $tren['7d']['labels']);
        $this->assertCount(30, $tren['30d']['labels']);
        $this->assertEquals(40000, array_sum($tren['7d']['cash']));
        $this->assertEquals(40000, array_sum($tren['30d']['cash']));
    }

    public function test_transactions_outside_the_window_are_not_counted_in_the_chart(): void
    {
        $this->transaksi('cash', 90000, now()->subDays(45)->toDateTimeString());

        $tren = app(ReportService::class)->getSalesTrend($this->event);

        $this->assertEquals(0, array_sum($tren['30d']['cash']));
    }

    public function test_only_paid_transactions_enter_the_chart(): void
    {
        $this->transaksi('cash', 30000, now()->toDateTimeString(), 'pending');

        $tren = app(ReportService::class)->getSalesTrend($this->event);

        $this->assertEquals(0, array_sum($tren['1d']['cash']));
    }
}
