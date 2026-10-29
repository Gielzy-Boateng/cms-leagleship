<?php

namespace App\Livewire\Programs;

use App\Enums\PublicationStatus;
use App\Models\Program;
use App\Support\ImageOptimizer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Program')]
class ProgramForm extends Component
{
    use WithFileUploads;

    public ?Program $program = null;

    #[Validate(['nullable', 'image', 'max:8192', 'mimes:jpg,jpeg,png,webp'], as: 'cover image')]
    public ?TemporaryUploadedFile $imageUpload = null;

    #[Validate(['nullable', 'boolean'])]
    public bool $removeImage = false;

    #[Validate(['nullable', 'date'])]
    public string $startsAt = '';

    #[Validate(['required', 'string', 'max:255'])]
    public string $title = '';

    #[Validate(['nullable', 'string', 'max:255'])]
    public string $slug = '';

    #[Validate(['nullable', 'string'])]
    public string $description = '';

    /** @var list<string> */
    public array $tags = [];

    public string $tagInput = '';

    #[Validate(['nullable', 'string', 'max:255', 'url'])]
    public string $link = '';

    #[Validate(['required', 'string'])]
    public string $status = PublicationStatus::Draft->value;

    public bool $isFeatured = false;

    public function mount(?Program $program = null): void
    {
        $this->program = $program;

        if ($program) {
            $this->title = $program->title;
            $this->slug = $program->slug;
            $this->description = $program->description ?? '';
            $this->tags = $program->tags ?? [];
            $this->startsAt = $program->starts_at?->format('Y-m-d') ?? '';
            $this->link = $program->link ?? '';
            $this->status = $program->status->value;
            $this->isFeatured = $program->is_featured;
        }
    }

    public function addTag(?string $value = null): void
    {
        $tag = trim($value ?? $this->tagInput);

        if ($tag === '') {
            return;
        }

        if (mb_strlen($tag) > 40) {
            $this->addError('tagInput', 'Each tag may be at most 40 characters.');

            return;
        }

        $exists = collect($this->tags)
            ->contains(fn (string $existing): bool => mb_strtolower($existing) === mb_strtolower($tag));

        if (! $exists) {
            $this->tags[] = $tag;
        }

        $this->tagInput = '';
        $this->resetErrorBag('tagInput');
    }

    public function removeTag(int $index): void
    {
        if (! array_key_exists($index, $this->tags)) {
            return;
        }

        array_splice($this->tags, $index, 1);
    }

    public function save(): void
    {
        if (trim($this->tagInput) !== '') {
            $this->addTag();
        }

        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'tags' => ['array', 'max:20'],
            'tags.*' => ['string', 'max:40'],
            'startsAt' => ['nullable', 'date'],
            'link' => ['nullable', 'string', 'max:255', 'url'],
            'status' => ['required', 'string'],
            'isFeatured' => ['boolean'],
            'imageUpload' => ['nullable', 'image', 'max:8192', 'mimes:jpg,jpeg,png,webp'],
            'removeImage' => ['nullable', 'boolean'],
        ]);

        $slug = Str::slug($this->slug);

        if ($slug !== '' && Program::slugTaken($slug, $this->program?->id)) {
            $this->addError('slug', 'That slug is already in use by another program.');

            return;
        }

        $tags = collect($this->tags)
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->unique(fn (string $tag): string => mb_strtolower($tag))
            ->values()
            ->all();

        $data = [
            'title' => $this->title,
            'description' => $this->description ?: null,
            'tags' => $tags === [] ? null : $tags,
            'starts_at' => $this->startsAt ? Carbon::parse($this->startsAt)->startOfDay() : null,
            'link' => $this->link ?: null,
            'status' => PublicationStatus::from($this->status),
            'is_featured' => $this->isFeatured,
        ];

        if ($slug !== '') {
            $data['slug'] = $slug;
        }

        if ($this->imageUpload) {
            try {
                $data['image'] = ImageOptimizer::store($this->imageUpload, 'programs');
            } catch (\Throwable $exception) {
                report($exception);
                $this->addError('imageUpload', 'Could not store that cover image. Try a smaller JPG/PNG/WebP under 8 MB.');

                return;
            }
        } elseif ($this->removeImage) {
            $this->deleteStoredImage();
            $data['image'] = null;
        }

        if ($this->imageUpload && $this->program?->image) {
            $this->deleteStoredImage($this->program->image);
        }

        $program = $this->program
            ? tap($this->program)->update($data)
            : Program::create($data);

        session()->flash('status', $this->program ? 'Program updated.' : 'Program created.');
        $this->redirectRoute('programs.edit', $program, navigate: true);
    }

    public function deleteStoredImage(?string $path = null): void
    {
        $path ??= $this->program?->image;

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function delete(): void
    {
        $this->program?->delete();

        session()->flash('status', 'Program deleted.');
        $this->redirectRoute('programs', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.programs.program-form');
    }
}
