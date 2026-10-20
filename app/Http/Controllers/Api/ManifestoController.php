<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Manifesto;
use Illuminate\Http\JsonResponse;

class ManifestoController extends Controller
{
    /**
     * Published manifestos, in display order.
     */
    public function index(): JsonResponse
    {
        $manifestos = Manifesto::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $manifestos->map(fn (Manifesto $manifesto) => $manifesto->toApiResponse()),
        ]);
    }

    /**
     * A single published manifesto, by slug or id.
     */
    public function show(string $manifesto): JsonResponse
    {
        $record = Manifesto::query()
            ->published()
            ->where(fn ($query) => $query->where('slug', $manifesto)->orWhere('id', $manifesto))
            ->firstOrFail();

        return response()->json([
            'data' => $record->toApiResponse(),
        ]);
    }
}
