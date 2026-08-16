<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEventRequest;
use App\Models\Event;
use App\Services\EventService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

            return redirect()->route('superadmin.events.index')->with('success', "Event '{$event->name}' berhasil dibuat!");
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

            return redirect()->route('superadmin.events.index')->with('success', "Event '{$event->name}' sekarang aktif!");
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
}
