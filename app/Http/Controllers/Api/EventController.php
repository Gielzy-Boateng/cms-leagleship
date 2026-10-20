<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Published events, soonest first.
     */
    public function index(Request $request): JsonResponse
    {
        $events = Event::query()
            ->published()
            ->when($request->boolean('upcoming'), fn ($query) => $query->where('starts_at', '>=', now()))
            ->orderBy('starts_at')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $events->map(fn (Event $event) => $event->toApiResponse()),
        ]);
    }

    /**
     * A single published event, by slug or id.
     */
    public function show(string $event): JsonResponse
    {
        $record = Event::query()
            ->published()
            ->where(fn ($query) => $query->where('slug', $event)->orWhere('id', $event))
            ->firstOrFail();

        return response()->json([
            'data' => $record->toApiResponse(),
        ]);
    }
}
