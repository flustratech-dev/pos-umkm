<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\HelpdeskReplyRequest;
use App\Http\Requests\HelpdeskTicketRequest;
use App\Models\HelpdeskReply;
use App\Models\HelpdeskTicket;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HelpdeskController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $store = $user->store ?: Store::where('owner_id', $user->id)->first();

        $tickets = $store 
            ? HelpdeskTicket::where('store_id', $store->id)
                ->with(['replies.user'])
                ->latest()
                ->get()
            : collect();

        return view('user.helpdesk', compact('user', 'store', 'tickets'));
    }

    public function store(HelpdeskTicketRequest $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $store = $user->store ?: Store::where('owner_id', $user->id)->firstOrFail();

        $ticketCode = 'TCK-' . now()->format('Ymd') . '-' . strtoupper(Str::random(3));

        $ticket = HelpdeskTicket::create([
            'ticket_code' => $ticketCode,
            'user_id' => $user->id,
            'store_id' => $store->id,
            'category' => $request->category,
            'subject' => $request->subject,
            'status' => 'open',
        ]);

        HelpdeskReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $request->message,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tiket bantuan berhasil dikirimkan ke panitia EO.',
                'ticket' => $ticket->load(['replies.user', 'store']),
            ]);
        }

        return redirect()->route('user.helpdesk')->with('success', 'Tiket bantuan berhasil dikirimkan ke panitia EO.');
    }

    public function reply(HelpdeskReplyRequest $request, HelpdeskTicket $ticket): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        if ($ticket->user_id !== $user->id && $ticket->store_id !== ($user->store_id ?: $user->ownedStore?->id)) {
            abort(403, 'Akses ditolak.');
        }

        $reply = HelpdeskReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $request->message,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Balasan berhasil dikirim.',
                'reply' => $reply->load('user'),
            ]);
        }

        return redirect()->route('user.helpdesk')->with('success', 'Balasan berhasil dikirim.');
    }
}
