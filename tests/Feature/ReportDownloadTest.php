<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Product;
use App\Models\RevenueSplit;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $superAdmin;
    protected User $tenantUser;
    protected Store $store;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin EO',
            'username' => 'admin_laporan',
            'email' => 'admin_laporan@example.com',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'username' => 'super_laporan',
            'email' => 'super_laporan@example.com',
            'role' => 'superadmin',
            'password' => bcrypt('password'),
        ]);

        $event = Event::create([
            'name' => 'Bazar Laporan',
            'slug' => 'bazar-laporan',
            'is_active' => true,
        ]);

        $this->tenantUser = User::create([
            'name' => 'Pemilik Stand',
            'username' => 'stand_laporan',
            'email' => 'stand_laporan@example.com',
            'role' => 'user',
            'password' => bcrypt('password'),
        ]);

        $this->store = Store::create([
            'event_id' => $event->id,
            'owner_id' => $this->tenantUser->id,
            'name' => 'Warung Laporan',
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

    /**
     * Transaksi pada tanggal & jam tertentu, seperti data yang sudah terlanjur
     * masuk sejak event berjalan.
     */
    protected function transaksiPada(string $waktu, float $total = 10000): Transaction
    {
        $saat = Carbon::parse($waktu);

        $transaction = Transaction::create([
            'invoice_code' => 'INV-' . $saat->format('Ymd') . '-' . strtoupper(substr(md5($waktu), 0, 4)),
            'store_id' => $this->store->id,
            'cashier_id' => $this->tenantUser->id,
            'total_amount' => $total,
            'payment_method' => 'cash',
            'amount_paid' => $total,
            'change_due' => 0,
            'status' => 'paid',
            'paid_at' => $saat,
        ]);

        // created_at ditulis langsung supaya bisa menguji transaksi hari lampau.
        Transaction::where('id', $transaction->id)->update([
            'created_at' => $saat,
            'updated_at' => $saat,
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $this->product->id,
            'title' => 'Nasi Goreng',
            'price' => $total,
            'qty' => 1,
            'subtotal' => $total,
        ]);

        RevenueSplit::create([
            'transaction_id' => $transaction->id,
            'owner_share' => $total * 0.75,
            'admin_gross_share' => $total * 0.25,
            'superadmin_share' => $total * 0.025,
            'admin_net_share' => $total * 0.225,
            'calculated_at' => $saat,
        ]);

        return $transaction->fresh();
    }

    public function test_transactions_from_previous_days_are_still_downloadable(): void
    {
        // Data yang sudah masuk sejak hari-hari sebelumnya harus tetap terambil.
        $this->transaksiPada('2026-08-16 11:36:00');
        $this->transaksiPada('2026-08-18 18:07:00');
        $this->transaksiPada('2026-08-21 08:04:00');

        $response = $this->actingAs($this->tenantUser)->get(
            route('user.laporan.pdf', ['format' => 'csv', 'from' => '2026-08-16', 'to' => '2026-08-21'])
        )->assertOk();

        $isi = $response->streamedContent();

        $this->assertStringContainsString('16/08/2026', $isi);
        $this->assertStringContainsString('18/08/2026', $isi);
        $this->assertStringContainsString('21/08/2026', $isi);
    }

    public function test_a_single_day_covers_midnight_to_end_of_day(): void
    {
        // Batasnya harus 00:00:00 sampai 23:59:59, bukan jam kerja saja.
        $this->transaksiPada('2026-08-20 00:00:05', 11000);
        $this->transaksiPada('2026-08-20 23:59:30', 12000);
        $this->transaksiPada('2026-08-21 00:00:10', 13000);

        $isi = $this->actingAs($this->tenantUser)->get(
            route('user.laporan.pdf', ['format' => 'csv', 'from' => '2026-08-20', 'to' => '2026-08-20'])
        )->assertOk()->streamedContent();

        $this->assertStringContainsString('20/08/2026 00:00', $isi);
        $this->assertStringContainsString('20/08/2026 23:59', $isi);
        $this->assertStringNotContainsString('21/08/2026 00:00', $isi);
    }

    public function test_only_one_date_still_reports_that_day(): void
    {
        $this->transaksiPada('2026-08-20 10:00:00');
        $this->transaksiPada('2026-08-21 10:00:00');

        $isi = $this->actingAs($this->tenantUser)->get(
            route('user.laporan.pdf', ['format' => 'csv', 'from' => '2026-08-20'])
        )->assertOk()->streamedContent();

        $this->assertStringContainsString('20/08/2026 10:00', $isi);
        $this->assertStringNotContainsString('21/08/2026 10:00', $isi);
    }

    public function test_reversed_dates_are_tidied_instead_of_failing(): void
    {
        $this->transaksiPada('2026-08-20 10:00:00');

        $this->actingAs($this->tenantUser)->get(
            route('user.laporan.pdf', ['format' => 'csv', 'from' => '2026-08-21', 'to' => '2026-08-19'])
        )->assertOk();
    }

    public function test_without_dates_every_period_is_included(): void
    {
        $this->transaksiPada('2026-08-16 11:36:00');
        $this->transaksiPada('2026-08-21 08:04:00');

        $isi = $this->actingAs($this->tenantUser)->get(
            route('user.laporan.pdf', ['format' => 'csv'])
        )->assertOk()->streamedContent();

        $this->assertStringContainsString('Semua Periode', $isi);
        $this->assertStringContainsString('16/08/2026', $isi);
        $this->assertStringContainsString('21/08/2026', $isi);
    }

    public function test_all_three_formats_are_produced(): void
    {
        $this->transaksiPada('2026-08-20 10:00:00');

        $pdf = $this->actingAs($this->tenantUser)->get(
            route('user.laporan.pdf', ['format' => 'pdf', 'from' => '2026-08-20', 'to' => '2026-08-20'])
        )->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $xlsx = $this->actingAs($this->tenantUser)->get(
            route('user.laporan.pdf', ['format' => 'xlsx', 'from' => '2026-08-20', 'to' => '2026-08-20'])
        )->assertOk();
        // Berkas xlsx adalah arsip zip, diawali "PK".
        $this->assertStringStartsWith('PK', $xlsx->streamedContent());

        $csv = $this->actingAs($this->tenantUser)->get(
            route('user.laporan.pdf', ['format' => 'csv', 'from' => '2026-08-20', 'to' => '2026-08-20'])
        )->assertOk();
        $this->assertStringContainsString('Kode Invoice', $csv->streamedContent());
    }

    public function test_file_name_carries_the_period(): void
    {
        $this->transaksiPada('2026-08-20 10:00:00');

        $this->actingAs($this->tenantUser)->get(
            route('user.laporan.pdf', ['format' => 'csv', 'from' => '2026-08-20', 'to' => '2026-08-20'])
        )->assertOk()->assertDownload('Laporan_Penjualan_Warung_Laporan_20260820.csv');
    }

    public function test_admin_can_download_a_period(): void
    {
        $this->transaksiPada('2026-08-20 10:00:00');
        $this->transaksiPada('2026-08-21 10:00:00');

        $isi = $this->actingAs($this->admin)->get(
            route('admin.laporan.pdf', ['format' => 'csv', 'from' => '2026-08-20', 'to' => '2026-08-20'])
        )->assertOk()->streamedContent();

        $this->assertStringContainsString('Warung Laporan', $isi);
        $this->assertStringContainsString('20/08/2026 10:00', $isi);
        $this->assertStringNotContainsString('21/08/2026 10:00', $isi);
    }

    public function test_superadmin_can_download_a_period(): void
    {
        $this->transaksiPada('2026-08-20 10:00:00');
        $this->transaksiPada('2026-08-21 10:00:00');

        $isi = $this->actingAs($this->superAdmin)->get(
            route('superadmin.laporan.pdf', ['format' => 'csv', 'from' => '2026-08-20', 'to' => '2026-08-20'])
        )->assertOk()->streamedContent();

        $this->assertStringContainsString('20/08/2026 10:00', $isi);
        $this->assertStringNotContainsString('21/08/2026 10:00', $isi);
    }

    public function test_report_pages_show_the_period_picker(): void
    {
        // Pemilih tanggal menyatu dengan baris filter lama, bukan panel terpisah.
        foreach (['user.laporan' => $this->tenantUser, 'admin.laporan' => $this->admin, 'superadmin.laporan' => $this->superAdmin] as $route => $aktor) {
            $html = $this->actingAs($aktor)->get(route($route))->assertOk()->getContent();

            $this->assertStringContainsString('Sampai tanggal', $html, $route);
            $this->assertStringContainsString('Hari Ini', $html, $route);
            $this->assertStringContainsString('7 Hari', $html, $route);
        }
    }

    public function test_report_page_list_follows_the_period(): void
    {
        $this->transaksiPada('2026-08-20 10:00:00', 12345);
        $this->transaksiPada('2026-08-21 10:00:00', 67890);

        $html = $this->actingAs($this->tenantUser)->get(
            route('user.laporan', ['from' => '2026-08-20', 'to' => '2026-08-20'])
        )->assertOk()->getContent();

        $this->assertStringContainsString('12345', $html);
        $this->assertStringNotContainsString('67890', $html);
    }
}
