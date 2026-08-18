<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Services\EventService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(
        protected EventService $eventService
    ) {}

    public function index(): View
    {
        $events = Event::with('stores')->latest()->get();
        $activeEvent = Event::getActive();

        return view('superadmin.events', compact('events', 'activeEvent'));
    }

    public function store(CreateEventRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $event = $this->eventService->createEvent($request->validated(), Auth::user());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Event '{$event->name}' berhasil dibuat!",
                    'event' => $event,
                ]);
            }

            return redirect()->route('admin.events.index')->with('success', "Event '{$event->name}' berhasil dibuat!");
        } catch (Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update(UpdateEventRequest $request, Event $event): JsonResponse|RedirectResponse
    {
        try {
            $event = $this->eventService->updateEvent($event, $request->validated());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Event '{$event->name}' berhasil diupdate!",
                    'event' => $event,
                ]);
            }

            return redirect()->route('admin.events.index')->with('success', "Event '{$event->name}' berhasil diupdate!");
        } catch (Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function activate(Event $event): JsonResponse|RedirectResponse
    {
        try {
            $this->eventService->activateEvent($event);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Event '{$event->name}' sekarang aktif sebagai event utama!",
                    'event' => $event->fresh(),
                ]);
            }

            return redirect()->route('admin.events.index')->with('success', "Event '{$event->name}' sekarang aktif!");
        } catch (Exception $e) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Event $event): JsonResponse|RedirectResponse
    {
        try {
            $this->eventService->deleteEvent($event);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Event berhasil dihapus!",
                ]);
            }

            return redirect()->route('admin.events.index')->with('success', "Event berhasil dihapus!");
        } catch (Exception $e) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle testing mode for an event.
     */
    public function toggleTesting(Request $request, Event $event, \App\Services\TestingModeService $testingModeService): JsonResponse|RedirectResponse
    {
        try {
            $state = $request->has('is_testing_mode') ? $request->boolean('is_testing_mode') : null;
            $newState = $testingModeService->toggleTestingMode($event, $state);

            $msg = $newState 
                ? "Masa Testing untuk event '{$event->name}' berhasil diaktifkan! Semua transaksi akan dicatat sebagai data uji coba."
                : "Masa Testing dinonaktifkan. Sistem siap untuk transaksi riil!";

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'is_testing_mode' => $newState,
                    'event' => $event->fresh(),
                ]);
            }

            return redirect()->back()->with('success', $msg);
        } catch (Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reset and wipe all testing transactions for an event.
     */
    public function resetTesting(Request $request, Event $event, \App\Services\TestingModeService $testingModeService): JsonResponse|RedirectResponse
    {
        try {
            $count = $testingModeService->resetTestingTransactions($event);

            $msg = "Berhasil membersihkan {$count} data transaksi testing pada event '{$event->name}'. Data stand, pemilik, dan produk tetap utuh!";

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'deleted_count' => $count,
                ]);
            }

            return redirect()->back()->with('success', $msg);
        } catch (Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
