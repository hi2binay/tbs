<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Event::query()->with('ticketTypes');

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('venue', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->where('starts_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('starts_at', '<=', $request->input('date_to'));
        }

        $sortBy = $request->input('sort_by', 'starts_at');
        $sortDirection = $request->input('sort_direction', 'asc');

        if (in_array($sortBy, ['starts_at', 'created_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $perPage = min($request->input('per_page', 15), 100);
        $events = $query->paginate($perPage);

        return response()->json($events);
    }

    public function show(string $slug): JsonResponse
    {
        $event = Event::where('slug', $slug)
            ->with(['ticketTypes' => function ($query) {
                $query->active();
            }])
            ->firstOrFail();

        return response()->json([
            'data' => $event,
        ]);
    }
}
