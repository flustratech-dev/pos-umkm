<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $events = Event::with('stores')->latest()->get();
        $activeEvent = Event::getActive();

        return view('superadmin.events', compact('events', 'activeEvent'));
    }
}
