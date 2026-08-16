<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HelpdeskReplyRequest;
use App\Models\Event;
use App\Models\HelpdeskReply;
use App\Models\HelpdeskTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HelpdeskController extends Controller
{
    public function index(Request $request): View
    {
        $activeEvent = Event::getActive();

        $query = HelpdeskTicket::with(['store', 'user', 'replies.user'])
            ->latest();

        if ($activeEvent) {
            $query->whereHas('store', function ($q) use ($activeEvent) {
                $q->where('event_id', $activeEvent->id);
            });
        }

        $tickets = $query->get();

        return view('admin.helpdesk', compact('activeEvent', 'tickets'));
    }

    public function updateStatus(Request $request, HelpdeskTicket $ticket): JsonResponse|RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved'],
        ]);

        $ticket->update(['status' => $request->status]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status tiket berhasil diperbarui.',
                'ticket' => $ticket,
            ]);
        }

        return redirect()->back()->with('success', 'Status tiket berhasil diperbarui.');
    }

    public function reply(HelpdeskReplyRequest $request, HelpdeskTicket $ticket): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        $reply = HelpdeskReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $request->message,
        ]);

        // Auto update status to in_progress if currently open
        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Balasan berhasil dikirim.',
                'reply' => $reply->load('user'),
                'ticket' => $ticket->fresh(),
            ]);
        }

        return redirect()->back()->with('success', 'Balasan berhasil dikirim.');
    }
}
