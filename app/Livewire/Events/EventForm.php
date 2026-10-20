<?php

namespace App\Livewire\Events;

use App\Enums\PublicationStatus;
use App\Models\Event;
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
#[Title('Event')]
class EventForm extends Component
{
    use WithFileUploads;

    public ?Event $event = null;

    #[Validate(['nullable', 'image', 'max:4096', 'mimes:jpg,jpeg,png,webp'], as: 'cover image')]
    public ?TemporaryUploadedFile $imageUpload = null;

    #[Validate(['nullable', 'boolean'])]
    public bool $removeImage = false;

    #[Validate(['required', 'string', 'max:255'])]
    public string $title = '';

    #[Validate(['nullable', 'string', 'max:255'])]
    public string $location = '';

    #[Validate(['nullable', 'string'])]
    public string $description = '';

    #[Validate(['nullable', 'date'])]
    public string $startsAt = '';

    #[Validate(['nullable', 'date'])]
    public string $endsAt = '';

    #[Validate(['required', 'string'])]
    public string $status = PublicationStatus::Draft->value;

    public function mount(?Event $event = null): void
    {
        $this->event = $event;

        if ($event) {
            $this->title = $event->title;
            $this->location = $event->location ?? '';
            $this->description = $event->description ?? '';
            $this->startsAt = $event->starts_at?->format('Y-m-d\TH:i') ?? '';
            $this->endsAt = $event->ends_at?->format('Y-m-d\TH:i') ?? '';
            $this->status = $event->status->value;
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'location' => $this->location ?: null,
            'description' => $this->description ?: null,
            'starts_at' => $this->startsAt ? Carbon::parse($this->startsAt) : null,
            'ends_at' => $this->endsAt ? Carbon::parse($this->endsAt) : null,
            'status' => PublicationStatus::from($this->status),
        ];

        if ($this->imageUpload) {
            $data['image'] = $this->imageUpload->store('events', 'public');
        } elseif ($this->removeImage) {
            $this->deleteStoredImage();
            $data['image'] = null;
        }

        if ($this->event) {
            if ($this->imageUpload && $this->event->image) {
                $this->deleteStoredImage($this->event->image);
            }

            $this->event->update($data);
            $this->reset('imageUpload', 'removeImage');
            session()->flash('status', 'Event updated.');
            $this->redirectRoute('events.edit', $this->event, navigate: true);

            return;
        }

        $event = Event::create($data);
        session()->flash('status', 'Event created.');
        $this->redirectRoute('events.edit', $event, navigate: true);
    }

    public function deleteStoredImage(?string $path = null): void
    {
        $path ??= $this->event?->image;

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function delete(): void
    {
        $this->event?->delete();

        session()->flash('status', 'Event deleted.');
        $this->redirectRoute('events', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.events.event-form');
    }
}
