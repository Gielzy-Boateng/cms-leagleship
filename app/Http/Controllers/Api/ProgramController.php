<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\JsonResponse;

class ProgramController extends Controller
{
    /**
     * Published programs.
     */
    public function index(): JsonResponse
    {
        $programs = Program::query()
            ->published()
            ->orderBy('title')
            ->get();

        return response()->json([
            'data' => $programs->map(fn (Program $program) => $program->toApiResponse()),
        ]);
    }

    /**
     * A single published program, by slug or id.
     */
    public function show(string $program): JsonResponse
    {
        $record = Program::query()
            ->published()
            ->where(fn ($query) => $query->where('slug', $program)->orWhere('id', $program))
            ->firstOrFail();

        return response()->json([
            'data' => $record->toApiResponse(),
        ]);
    }
}
