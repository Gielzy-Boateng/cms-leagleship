<?php

namespace App\Livewire\Programs;

use App\Enums\PublicationStatus;
use App\Models\Program;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
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

    #[Validate(['nullable', 'image', 'max:4096', 'mimes:jpg,jpeg,png,webp'], as: 'cover image')]
    public ?TemporaryUploadedFile $imageUpload = null;

    #[Validate(['nullable', 'boolean'])]
    public bool $removeImage = false;

    #[Validate(['nullable', 'date'])]
    public string $startsAt = '';

    #[Validate(['required', 'string', 'max:255'])]
    public string $title = '';

    #[Validate(['nullable', 'string'])]
    public string $description = '';

    #[Validate(['nullable', 'string', 'max:255', 'url'])]
    public string $link = '';

    #[Validate(['required', 'string'])]
    public string $status = PublicationStatus::Draft->value;

    public function mount(?Program $program = null): void
    {
        $this->program = $program;

        if ($program) {
            $this->title = $program->title;
            $this->description = $program->description ?? '';
            $this->startsAt = $program->starts_at?->format('Y-m-d') ?? '';
            $this->link = $program->link ?? '';
            $this->status = $program->status->value;
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'description' => $this->description ?: null,
            'starts_at' => $this->startsAt ? Carbon::parse($this->startsAt)->startOfDay() : null,
            'link' => $this->link ?: null,
            'status' => PublicationStatus::from($this->status),
        ];

        if ($this->imageUpload) {
            $data['image'] = $this->imageUpload->store('programs', 'public');
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
