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

    #[Validate(['required', 'string', 'max:5000'])]
    public string $quote = '';

    #[Validate(['required', 'string', 'max:255'])]
    public string $name = '';

    #[Validate(['nullable', 'string', 'max:255'])]
    public string $role = '';

    #[Validate(['nullable', 'string', 'max:255'])]
    public string $source = '';

    #[Validate(['required', 'integer', 'min:0'])]
    public string $sortOrder = '0';

    #[Validate(['required', 'string'])]
    public string $status = PublicationStatus::Draft->value;

    #[Validate(['nullable', 'image', 'max:4096', 'mimes:jpg,jpeg,png,webp'], as: 'portrait')]
    public ?TemporaryUploadedFile $imageUpload = null;

    #[Validate(['nullable', 'boolean'])]
    public bool $removeImage = false;

    public function mount(?Testimonial $testimonial = null): void
    {
        $this->testimonial = $testimonial;

        if ($testimonial) {
            $this->quote = $testimonial->quote;
            $this->name = $testimonial->name;
            $this->role = $testimonial->role ?? '';
            $this->source = $testimonial->source ?? '';
            $this->sortOrder = (string) $testimonial->sort_order;
            $this->status = $testimonial->status->value;

            return;
        }

        $this->sortOrder = (string) (Testimonial::query()->max('sort_order') + 1);
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'quote' => $this->quote,
            'name' => $this->name,
            'role' => $this->role ?: null,
            'source' => $this->source ?: null,
            'sort_order' => (int) $this->sortOrder,
            'status' => PublicationStatus::from($this->status),
        ];

        if ($this->imageUpload) {
            $data['image'] = ImageOptimizer::store($this->imageUpload, 'testimonials');
        } elseif ($this->removeImage) {
            $this->deleteStoredImage();
            $data['image'] = null;
        }

        if ($this->imageUpload && $this->testimonial?->image) {
            $this->deleteStoredImage($this->testimonial->image);
        }

        $testimonial = $this->testimonial
            ? tap($this->testimonial)->update($data)
            : Testimonial::create($data);

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
