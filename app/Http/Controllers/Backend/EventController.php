<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::withCount('ticketTypes')->latest()->paginate(15);

        return view('backend.events.index', compact('events'));
    }

    public function create()
    {
        return view('backend.events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'venue' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'status' => 'required|in:draft,published,cancelled',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
        ]);

        $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(6);

        Event::create($validated);

        return redirect()->route('backend.events.index')
            ->with('success', 'Event created successfully.');
    }

    public function edit(Event $event)
    {
        return view('backend.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'venue' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'status' => 'required|in:draft,published,cancelled',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
        ]);

        if ($event->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(6);
        }

        $event->update($validated);

        return redirect()->route('backend.events.index')
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->route('backend.events.index')
            ->with('success', 'Event deleted successfully.');
    }
}
