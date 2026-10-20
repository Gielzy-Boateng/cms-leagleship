<?php

namespace App\Livewire\Pages;

use App\Enums\PublicationStatus;
use App\Models\Page;
use App\Support\PageBlocks;
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
#[Title('Page')]
class PageForm extends Component
{
    use WithFileUploads;

    public ?Page $page = null;

    #[Validate(['required', 'string', 'max:255'])]
    public string $title = '';

    #[Validate(['required', 'string'])]
    public string $status = PublicationStatus::Draft->value;

    /**
     * Editor state: each block carries an internal id used to key pending uploads.
     *
     * @var array<int, array<string, mixed>>
     */
    #[Validate(['required', 'array', 'min:1'])]
    public array $blocks = [];

    /**
     * @var array<string, TemporaryUploadedFile>
     */
    public array $imageUploads = [];

    public function mount(?Page $page = null): void
    {
        $this->page = $page;

        if ($page) {
            $this->title = $page->title;
            $this->status = $page->status->value;
            $this->blocks = $this->withIds($page->blocks());

            return;
        }

        $this->blocks = [PageBlocks::blank('heading')];
    }

    public function addBlock(string $type): void
    {
        $this->blocks[] = PageBlocks::blank($type) + ['id' => (string) Str::uuid()];
    }

    public function removeBlock(int $index): void
    {
        $id = $this->blocks[$index]['id'] ?? null;

        unset($this->blocks[$index], $this->imageUploads[$id]);

        $this->blocks = array_values($this->blocks);
    }

    public function moveBlock(int $index, string $direction): void
    {
        $target = $direction === 'up' ? $index - 1 : $index + 1;

        if (! isset($this->blocks[$target])) {
            return;
        }

        [$this->blocks[$index], $this->blocks[$target]] = [$this->blocks[$target], $this->blocks[$index]];
    }

    public function save(): void
    {
        $this->validate();
        $this->validate(array_merge(PageBlocks::rules(), [
            'imageUploads.*' => ['nullable', 'image', 'max:4096', 'mimes:jpg,jpeg,png,webp'],
        ]));

        $previousImages = $this->page?->imagePaths() ?? [];

        $blocks = $this->storeImages();
        $normalized = PageBlocks::normalize($blocks);

        if ($normalized === []) {
            $this->addError('blocks', 'Add at least one block that carries content.');

            return;
        }

        $data = [
            'title' => $this->title,
            'status' => PublicationStatus::from($this->status),
            'content' => $normalized,
        ];

        $page = $this->page
            ? tap($this->page)->update($data)
            : Page::create($data);

        $this->deleteImages(array_values(array_diff($previousImages, $page->imagePaths())));

        $this->reset('imageUploads');

        session()->flash('status', $this->page ? 'Page updated.' : 'Page created.');
        $this->redirectRoute('pages.edit', $page, navigate: true);
    }

    public function delete(): void
    {
        $this->page?->purgeImages();
        $this->page?->delete();

        session()->flash('status', 'Page deleted.');
        $this->redirectRoute('pages', navigate: true);
    }

    /**
     * Persist pending uploads; the images the page no longer references are pruned on save.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function storeImages(): array
    {
        $blocks = $this->blocks;

        foreach ($blocks as $index => $block) {
            if ($block['type'] !== 'image') {
                continue;
            }

            $upload = $this->imageUploads[$block['id'] ?? ''] ?? null;

            if (! $upload instanceof TemporaryUploadedFile) {
                continue;
            }

            $blocks[$index]['src'] = $upload->store('pages', 'public');
        }

        return $blocks;
    }

    /**
     * @param  array<int, string>  $paths
     */
    protected function deleteImages(array $paths): void
    {
        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    protected function withIds(array $blocks): array
    {
        return array_map(fn (array $block): array => [...$block, 'id' => (string) Str::uuid()], $blocks);
    }

    public function render(): View
    {
        return view('livewire.pages.page-form', [
            'blockTypes' => PageBlocks::TYPES,
        ]);
    }
}
