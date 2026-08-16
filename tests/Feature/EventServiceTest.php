<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Services\EventService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EventService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EventService();
    }

    public function test_create_and_activate_event(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'email' => 'super@test.com',
            'role' => 'superadmin',
            'password' => bcrypt('password'),
        ]);

        $event1 = $this->service->createEvent([
            'name' => 'Event Pertama',
            'is_active' => true,
        ], $superadmin);

        $this->assertTrue($event1->is_active);
        $this->assertEquals($event1->id, Event::getActive()->id);

        $event2 = $this->service->createEvent([
            'name' => 'Event Kedua',
            'is_active' => false,
        ], $superadmin);

        $this->assertFalse($event2->is_active);
        $this->assertEquals($event1->id, Event::getActive()->id);

        // Activate event2
        $this->service->activateEvent($event2);

        $this->assertFalse($event1->fresh()->is_active);
        $this->assertTrue($event2->fresh()->is_active);
        $this->assertEquals($event2->id, Event::getActive()->id);
    }
}
