<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;

class TestimonialController extends Controller
{
    /**
     * Published testimonials, in display order.
     */
    public function index(): JsonResponse
    {
        $testimonials = Testimonial::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $testimonials->map(fn (Testimonial $testimonial) => $testimonial->toApiResponse()),
        ]);
    }

    /**
     * A single published testimonial, by id.
     */
    public function show(string $testimonial): JsonResponse
    {
        $record = Testimonial::query()
            ->published()
            ->where('id', $testimonial)
            ->firstOrFail();

        return response()->json([
            'data' => $record->toApiResponse(),
        ]);
    }
}
