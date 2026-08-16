<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventService
{
    /**
     * Create a new event.
     */
    public function createEvent(array $data, ?User $creator = null): Event
    {
        return DB::transaction(function () use ($data, $creator) {
            $slug = Str::slug($data['name']);
            $count = Event::where('slug', 'like', "{$slug}%")->count();
            if ($count > 0) {
                $slug .= '-' . ($count + 1);
            }

            $isActive = !empty($data['is_active']);

            if ($isActive) {
                Event::query()->update(['is_active' => false]);
            }

            return Event::create([
                'name' => $data['name'],
                'slug' => $slug,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'location' => $data['location'] ?? null,
                'is_active' => $isActive,
                'created_by' => $creator?->id,
            ]);
        });
    }

    /**
     * Set an event as active atomically and deactivate all other events.
     */
    public function activateEvent(Event $event): void
    {
        DB::transaction(function () use ($event) {
            Event::query()->update(['is_active' => false]);
            $event->update(['is_active' => true]);
        });
    }
}
