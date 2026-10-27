<?php

namespace App\Livewire\Manifestos;

use App\Enums\PublicationStatus;
use App\Models\Manifesto;
use App\Support\ImageOptimizer;
use App\Support\RichText;
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
#[Title('Manifesto')]
class ManifestoForm extends Component
{
    use WithFileUploads;

    public ?Manifesto $manifesto = null;

    #[Validate(['required', 'string', 'max:255'])]
    public string $title = '';

    #[Validate(['nullable', 'string', 'max:255'])]
    public string $slug = '';

    #[Validate(['nullable', 'string', 'max:255'])]
    public string $excerpt = '';

    #[Validate(['required', 'string', 'max:100000'])]
    public string $body = '';

    #[Validate(['required', 'integer', 'min:0'])]
    public string $sortOrder = '0';

    #[Validate(['required', 'string'])]
    public string $status = PublicationStatus::Draft->value;

    #[Validate(['nullable', 'image', 'max:4096', 'mimes:jpg,jpeg,png,webp'], as: 'image')]
    public ?TemporaryUploadedFile $imageUpload = null;

    #[Validate(['nullable', 'boolean'])]
    public bool $removeImage = false;

    public function mount(?Manifesto $manifesto = null): void
    {
        $this->manifesto = $manifesto;

        if ($manifesto) {
            $this->title = $manifesto->title;
            $this->slug = $manifesto->slug;
            $this->excerpt = $manifesto->excerpt ?? '';
            $this->body = $manifesto->body;
            $this->sortOrder = (string) $manifesto->sort_order;
            $this->status = $manifesto->status->value;

            return;
        }

        $this->sortOrder = (string) (Manifesto::query()->max('sort_order') + 1);
    }

    public function save(): void
    {
        $this->validate();

        $slug = Str::slug($this->slug);

        if ($slug !== '' && Manifesto::slugTaken($slug, $this->manifesto?->id)) {
            $this->addError('slug', 'That slug is already in use by another entry.');

            return;
        }

        $body = RichText::sanitize($this->body);

        if (RichText::toPlainText($body) === '') {
            $this->addError('body', 'The body needs some text.');

            return;
        }

        $data = [
            'title' => $this->title,
            'excerpt' => $this->excerpt ?: null,
            'body' => $body,
            'sort_order' => (int) $this->sortOrder,
            'status' => PublicationStatus::from($this->status),
        ];

        if ($slug !== '') {
            $data['slug'] = $slug;
        }

        if ($this->imageUpload) {
            $data['image'] = ImageOptimizer::store($this->imageUpload, 'manifestos');
        } elseif ($this->removeImage) {
            $this->deleteStoredImage();
            $data['image'] = null;
        }

        if ($this->imageUpload && $this->manifesto?->image) {
            $this->deleteStoredImage($this->manifesto->image);
        }

        $manifesto = $this->manifesto
            ? tap($this->manifesto)->update($data)
            : Manifesto::create($data);

        session()->flash('status', $this->manifesto ? 'Manifesto updated.' : 'Manifesto created.');
        $this->redirectRoute('manifestos.edit', $manifesto, navigate: true);
    }

    public function deleteStoredImage(?string $path = null): void
    {
        $path ??= $this->manifesto?->image;

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function delete(): void
    {
        $this->manifesto?->delete();

        session()->flash('status', 'Manifesto deleted.');
        $this->redirectRoute('manifestos', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.manifestos.manifesto-form');
    }
}
