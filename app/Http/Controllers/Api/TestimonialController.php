<?php

namespace App\Http\Controllers\Api;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Support\ImageOptimizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

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

    /**
     * Add a testimonial card. The caller must send the CMS admin's email
     * and password as HTTP basic credentials. A card is published unless
     * status is set to draft, so the public list can load it straight away.
     */
    public function store(Request $request): JsonResponse
    {
        /** @var array{quote: string, name: string, role?: string|null, source?: string|null, sort_order?: int|null, status?: string|null} $validated */
        $validated = $request->validate([
            'quote' => ['required', 'string', 'max:5000'],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', Rule::enum(PublicationStatus::class)],
            'image' => ['nullable', 'image', 'max:4096', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $upload = $request->file('image');
        $status = PublicationStatus::tryFrom((string) ($validated['status'] ?? ''))
            ?? PublicationStatus::Published;

        $testimonial = Testimonial::create([
            'quote' => $validated['quote'],
            'name' => $validated['name'],
            'role' => $validated['role'] ?? null,
            'source' => $validated['source'] ?? null,
            'sort_order' => $validated['sort_order'] ?? ((int) Testimonial::query()->max('sort_order') + 1),
            'status' => $status,
            'image' => $upload instanceof UploadedFile
                ? ImageOptimizer::store($upload, 'testimonials')
                : null,
        ]);

        return response()->json([
            'data' => $testimonial->toApiResponse(),
        ], 201);
    }
}
