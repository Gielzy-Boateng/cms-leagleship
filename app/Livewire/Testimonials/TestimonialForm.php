<?php

namespace App\Livewire\Testimonials;

use App\Enums\PublicationStatus;
use App\Models\Testimonial;
use App\Support\ImageOptimizer;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Testimonial')]
class TestimonialForm extends Component
{
    use WithFileUploads;

    public ?Testimonial $testimonial = null;

    #[Validate(['required', 'integer', 'min:0'])]
    public string $sortOrder = '0';

    #[Validate(['required', 'string'])]
    public string $status = PublicationStatus::Draft->value;

    /** Single replace upload when editing one card. */
    public ?TemporaryUploadedFile $imageUpload = null;

    /**
     * Batch create uploads — each file becomes its own testimonial card.
     *
     * @var array<int, TemporaryUploadedFile>
     */
    public array $imageUploads = [];

    public function mount(?Testimonial $testimonial = null): void
    {
        $this->testimonial = $testimonial;

        if ($testimonial) {
            $this->sortOrder = (string) $testimonial->sort_order;
            $this->status = $testimonial->status->value;

            return;
        }

        $this->sortOrder = (string) (Testimonial::query()->max('sort_order') + 1);
    }

    public function removeBatchImage(int $index): void
    {
        if (! array_key_exists($index, $this->imageUploads)) {
            return;
        }

        array_splice($this->imageUploads, $index, 1);
    }

    public function clearBatchImages(): void
    {
        $this->imageUploads = [];
        $this->resetErrorBag('imageUploads');
        $this->resetErrorBag('imageUploads.*');
    }

    public function save(): void
    {
        if ($this->testimonial) {
            $this->saveExisting();

            return;
        }

        $this->saveBatch();
    }

    protected function saveExisting(): void
    {
        $this->validate([
            'sortOrder' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'string'],
            'imageUpload' => ['nullable', 'image', 'max:8192', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $data = [
            'sort_order' => (int) $this->sortOrder,
            'status' => PublicationStatus::from($this->status),
        ];

        if ($this->imageUpload) {
            try {
                $data['image'] = ImageOptimizer::store($this->imageUpload, 'testimonials');
            } catch (\Throwable $exception) {
                report($exception);
                $this->addError('imageUpload', 'Could not store that card image. Try a smaller JPG/PNG/WebP under 8 MB.');

                return;
            }

            if ($this->testimonial?->image) {
                $this->deleteStoredImage($this->testimonial->image);
            }
        }

        try {
            $this->testimonial->update($data);
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('imageUpload', 'Could not save this testimonial. Check storage/logs/laravel.log.');

            return;
        }

        session()->flash('status', 'Testimonial updated.');
        $this->redirectRoute('testimonials.edit', $this->testimonial, navigate: true);
    }

    protected function saveBatch(): void
    {
        $this->validate([
            'sortOrder' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'string'],
            'imageUploads' => ['required', 'array', 'min:1', 'max:20'],
            'imageUploads.*' => ['required', 'image', 'max:8192', 'mimes:jpg,jpeg,png,webp'],
        ], [
            'imageUploads.required' => 'Add at least one Canva card image.',
            'imageUploads.min' => 'Add at least one Canva card image.',
            'imageUploads.max' => 'Upload at most 20 cards at a time.',
        ]);

        $status = PublicationStatus::from($this->status);
        $sortOrder = (int) $this->sortOrder;
        $created = 0;
        $failed = 0;

        foreach ($this->imageUploads as $index => $upload) {
            try {
                Testimonial::create([
                    'image' => ImageOptimizer::store($upload, 'testimonials'),
                    'sort_order' => $sortOrder + $created,
                    'status' => $status,
                ]);
                $created++;
            } catch (\Throwable $exception) {
                report($exception);
                $failed++;
                $this->addError(
                    'imageUploads.'.$index,
                    'Could not save “'.($upload->getClientOriginalName() ?: 'card').'”. Try a smaller JPG/PNG/WebP under 8 MB.'
                );
            }
        }

        if ($created === 0) {
            $this->addError('imageUploads', 'No cards were saved. Check the files and try again.');

            return;
        }

        $message = $created === 1
            ? '1 testimonial card created.'
            : "{$created} testimonial cards created.";

        if ($failed > 0) {
            $message .= " {$failed} failed.";
        }

        session()->flash('status', $message);
        $this->redirectRoute('testimonials', navigate: true);
    }

    public function deleteStoredImage(?string $path = null): void
    {
        $path ??= $this->testimonial?->image;

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function delete(): void
    {
        $this->testimonial?->purgeImage();
        $this->testimonial?->delete();

        session()->flash('status', 'Testimonial deleted.');
        $this->redirectRoute('testimonials', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.testimonials.testimonial-form');
    }
}
