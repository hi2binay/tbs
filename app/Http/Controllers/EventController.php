<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::where('status', 'published');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('description', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('sort')) {
            match ($request->sort) {
                'date_asc' => $query->orderBy('starts_at', 'asc'),
                'date_desc' => $query->orderBy('starts_at', 'desc'),
                'title' => $query->orderBy('name', 'asc'),
                default => $query->orderBy('starts_at', 'asc'),
            };
        } else {
            $query->orderBy('starts_at', 'asc');
        }

        $events = $query->paginate(12);
        $categories = [];

        return view('events.index', compact('events', 'categories'));
    }

    public function show(Event $event)
    {
        $event->load(['ticketTypes']);

        return view('events.show', compact('event'));
    }
}
