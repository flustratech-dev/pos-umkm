<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventSaveTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin EO',
            'username' => 'admin_event',
            'email' => 'admin_event@example.com',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);
    }

    protected function makeEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'name' => 'Bazar Lama',
            'slug' => 'bazar-lama',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-03',
            'location' => 'Lapangan Kota',
            'qris_payload' => '000201010211PAYLOADLAMA',
            'is_active' => true,
            'created_by' => $this->admin->id,
        ], $overrides));
    }

    public function test_admin_can_create_event_with_schedule(): void
    {
        $this->actingAs($this->admin)->post(route('admin.events.store'), [
            'name' => 'Bazar Kemerdekaan',
            'location' => 'Alun-alun',
            'start_date' => '2026-08-20',
            'end_date' => '2026-08-22',
        ])->assertRedirect(route('admin.events.index'));

        $event = Event::where('name', 'Bazar Kemerdekaan')->first();

        $this->assertNotNull($event);
        $this->assertEquals('2026-08-20', $event->start_date->toDateString());
        $this->assertEquals('2026-08-22', $event->end_date->toDateString());
        $this->assertEquals('Alun-alun', $event->location);
    }

    public function test_event_page_sends_schedule_and_qris_to_the_browser(): void
    {
        $event = $this->makeEvent();

        $html = $this->actingAs($this->admin)->get(route('admin.events.index'))
            ->assertOk()
            ->getContent();

        preg_match('/window\.__INITIAL_EVENTS__ = (\[.*?\]);/s', $html, $m);
        $this->assertNotEmpty($m, 'Payload __INITIAL_EVENTS__ tidak ditemukan.');

        $payload = collect(json_decode($m[1], true))->firstWhere('id', $event->id);

        // Tanpa field ini, kartu event selalu menampilkan "Jadwal belum diatur"
        // dan modal edit terbuka dengan tanggal & payload QRIS kosong.
        $this->assertEquals('2026-09-01', $payload['start_date']);
        $this->assertEquals('2026-09-03', $payload['end_date']);
        $this->assertEquals('000201010211PAYLOADLAMA', $payload['qris_payload']);
    }

    public function test_editing_event_saves_new_schedule(): void
    {
        $event = $this->makeEvent();

        $this->actingAs($this->admin)->post(route('admin.events.update', $event), [
            '_method' => 'PUT',
            'name' => 'Bazar Baru',
            'location' => 'Gedung Serbaguna',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-12',
            'qris_payload' => '000201010211PAYLOADLAMA',
        ])->assertRedirect(route('admin.events.index'));

        $event->refresh();

        $this->assertEquals('Bazar Baru', $event->name);
        $this->assertEquals('Gedung Serbaguna', $event->location);
        $this->assertEquals('2026-10-10', $event->start_date->toDateString());
        $this->assertEquals('2026-10-12', $event->end_date->toDateString());
        $this->assertEquals('000201010211PAYLOADLAMA', $event->qris_payload);
    }

    public function test_editing_event_without_qris_field_keeps_existing_payload(): void
    {
        $event = $this->makeEvent();

        $this->actingAs($this->admin)->post(route('admin.events.update', $event), [
            '_method' => 'PUT',
            'name' => 'Bazar Lama Revisi',
        ])->assertRedirect(route('admin.events.index'));

        $event->refresh();

        $this->assertEquals('Bazar Lama Revisi', $event->name);
        $this->assertEquals('000201010211PAYLOADLAMA', $event->qris_payload);
        $this->assertEquals('2026-09-01', $event->start_date->toDateString());
        $this->assertEquals('Lapangan Kota', $event->location);
    }

    public function test_editing_event_can_clear_the_schedule(): void
    {
        $event = $this->makeEvent();

        $this->actingAs($this->admin)->post(route('admin.events.update', $event), [
            '_method' => 'PUT',
            'name' => 'Bazar Lama',
            'location' => 'Lapangan Kota',
            'start_date' => '',
            'end_date' => '',
            'qris_payload' => '000201010211PAYLOADLAMA',
        ])->assertRedirect(route('admin.events.index'));

        $event->refresh();

        $this->assertNull($event->start_date);
        $this->assertNull($event->end_date);
    }

    public function test_invalid_date_range_is_reported_back_to_the_user(): void
    {
        $event = $this->makeEvent();

        $this->actingAs($this->admin)->post(route('admin.events.update', $event), [
            '_method' => 'PUT',
            'name' => 'Bazar Lama',
            'start_date' => '2026-09-10',
            'end_date' => '2026-09-01',
        ])->assertSessionHasErrors('end_date');

        $this->assertEquals('Bazar Lama', $event->fresh()->name);
    }
}
