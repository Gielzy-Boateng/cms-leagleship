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

    public ?TemporaryUploadedFile $imageUpload = null;

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

    public function save(): void
    {
        $rules = [
            'sortOrder' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'string'],
            'imageUpload' => $this->testimonial
                ? ['nullable', 'image', 'max:8192', 'mimes:jpg,jpeg,png,webp']
                : ['required', 'image', 'max:8192', 'mimes:jpg,jpeg,png,webp'],
        ];

        $this->validate($rules);

        $data = [
            'sort_order' => (int) $this->sortOrder,
            'status' => PublicationStatus::from($this->status),
        ];

        if ($this->imageUpload) {
            try {
                // Canva cards are finished assets — store as uploaded. Re-encoding
                // large PNGs with GD often fatals shared-hosting memory limits.
                $data['image'] = ImageOptimizer::storeOriginal($this->imageUpload, 'testimonials');
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
            $testimonial = $this->testimonial
                ? tap($this->testimonial)->update($data)
                : Testimonial::create($data);
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('imageUpload', 'Could not save this testimonial. Ask the developer to check storage/logs/laravel.log.');

            return;
        }

        session()->flash('status', $this->testimonial ? 'Testimonial updated.' : 'Testimonial created.');
        $this->redirectRoute('testimonials.edit', $testimonial, navigate: true);
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
