<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    /**
     * Published pages with their rendered blocks.
     */
    public function index(): JsonResponse
    {
        $pages = Page::query()
            ->published()
            ->orderBy('title')
            ->get();

        return response()->json([
            'data' => $pages->map(fn (Page $page) => $page->toApiResponse()),
        ]);
    }

    /**
     * A single published page, by slug or id.
     */
    public function show(string $page): JsonResponse
    {
        $record = Page::query()
            ->published()
            ->where(fn ($query) => $query->where('slug', $page)->orWhere('id', $page))
            ->firstOrFail();

        return response()->json([
            'data' => $record->toApiResponse(),
        ]);
    }
}
