<?php

namespace App\Http\Controllers\Api;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Support\ImageOptimizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TestimonialController extends Controller
{
    /**
     * Published testimonial cards, in display order.
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
     * A single published testimonial card, by id.
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

    /**
     * Upload a Canva testimonial card image. The caller must send the CMS
     * admin's email and password as HTTP basic credentials. The card is
     * published unless status is set to draft.
     */
    public function store(Request $request): JsonResponse
    {
        /** @var array{sort_order?: int|null, status?: string|null} $validated */
        $validated = $request->validate([
            'image' => ['required', 'image', 'max:8192', 'mimes:jpg,jpeg,png,webp'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', Rule::enum(PublicationStatus::class)],
        ]);

        $status = PublicationStatus::tryFrom((string) ($validated['status'] ?? ''))
            ?? PublicationStatus::Published;

        $testimonial = Testimonial::create([
            'image' => ImageOptimizer::store($request->file('image'), 'testimonials'),
            'sort_order' => $validated['sort_order'] ?? ((int) Testimonial::query()->max('sort_order') + 1),
            'status' => $status,
        ]);

        return response()->json([
            'data' => $testimonial->toApiResponse(),
        ], 201);
    }
}
