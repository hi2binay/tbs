<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\Request;

class TicketTypeController extends Controller
{
    public function create(Event $event)
    {
        return view('backend.ticket-types.create', compact('event'));
    }

    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_cents' => 'required|integer|min:0',
            'total_quantity' => 'required|integer|min:1',
        ]);

        $event->ticketTypes()->create($validated);

        return redirect()->route('backend.events.edit', $event)
            ->with('success', 'Ticket type created successfully.');
    }

    public function edit(Event $event, TicketType $ticketType)
    {
        abort_if($ticketType->event_id !== $event->id, 404);

        return view('backend.ticket-types.edit', compact('event', 'ticketType'));
    }

    public function update(Request $request, Event $event, TicketType $ticketType)
    {
        abort_if($ticketType->event_id !== $event->id, 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_cents' => 'required|integer|min:0',
            'total_quantity' => 'required|integer|min:1',
        ]);

        $ticketType->update($validated);

        return redirect()->route('backend.events.edit', $event)
            ->with('success', 'Ticket type updated successfully.');
    }

    public function destroy(Event $event, TicketType $ticketType)
    {
        abort_if($ticketType->event_id !== $event->id, 404);

        $ticketType->delete();

        return redirect()->route('backend.events.edit', $event)
            ->with('success', 'Ticket type deleted successfully.');
    }
}
