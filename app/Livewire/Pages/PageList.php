<?php

namespace App\Livewire\Pages;

use App\Enums\PublicationStatus;
use App\Livewire\Concerns\SendsToasts;
use App\Models\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Pages')]
class PageList extends Component
{
    use SendsToasts;
    use WithPagination;

    #[Url]
    public string $search = '';

    /**
     * @return Builder<Page>
     */
    protected function pagesQuery(): Builder
    {
        return Page::query()
            ->when($this->search, function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->where('title', 'like', "%{$this->search}%")
                        ->orWhere('slug', 'like', "%{$this->search}%")
                        ->orWhere('content', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('title');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(Page $page): void
    {
        $page->update([
            'status' => $page->status === PublicationStatus::Published
                ? PublicationStatus::Draft
                : PublicationStatus::Published,
        ]);

        $this->jsToast($page->status === PublicationStatus::Published
            ? "{$page->title} published."
            : "{$page->title} moved to drafts.");
    }

    public function destroy(Page $page): void
    {
        $title = $page->title;
        $page->purgeImages();
        $page->delete();

        $this->jsToast("{$title} deleted.");
    }

    public function render(): View
    {
        return view('livewire.pages.page-list', [
            'pages' => $this->pagesQuery()->paginate(10),
        ]);
    }
}
