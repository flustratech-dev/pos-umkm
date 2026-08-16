<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

            $qrisImagePath = null;
            if (isset($data['qris_image']) && $data['qris_image'] instanceof \Illuminate\Http\UploadedFile) {
                $qrisImagePath = $data['qris_image']->store('events/qris', 'public');
            }

            return Event::create([
                'name' => $data['name'],
                'slug' => $slug,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'location' => $data['location'] ?? null,
                'is_active' => $isActive,
                'qris_image' => $qrisImagePath,
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

    public function updateEvent(Event $event, array $data): Event
    {
        return DB::transaction(function () use ($event, $data) {
            $updateData = [
                'name' => $data['name'] ?? $event->name,
                'start_date' => $data['start_date'] ?? $event->start_date,
                'end_date' => $data['end_date'] ?? $event->end_date,
                'location' => $data['location'] ?? $event->location,
            ];

            if (isset($data['qris_image']) && $data['qris_image'] instanceof \Illuminate\Http\UploadedFile) {
                if ($event->qris_image && Storage::disk('public')->exists($event->qris_image)) {
                    Storage::disk('public')->delete($event->qris_image);
                }
                $updateData['qris_image'] = $data['qris_image']->store('events/qris', 'public');
            }

            $event->update($updateData);

            return $event;
        });
    }

    /**
     * Delete an event.
     */
    public function deleteEvent(Event $event): void
    {
        DB::transaction(function () use ($event) {
            if ($event->is_active) {
                throw new \Exception('Tidak bisa menghapus event yang sedang aktif!');
            }
            if ($event->qris_image && Storage::disk('public')->exists($event->qris_image)) {
                Storage::disk('public')->delete($event->qris_image);
            }
            $event->delete();
        });
    }
}
