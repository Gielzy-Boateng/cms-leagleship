<?php

namespace App\Livewire\Manifestos;

use App\Enums\PublicationStatus;
use App\Livewire\Concerns\SendsToasts;
use App\Models\Manifesto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Manifestos')]
class ManifestoList extends Component
{
    use SendsToasts;
    use WithPagination;

    #[Url]
    public string $search = '';

    /**
     * @return Builder<Manifesto>
     */
    protected function manifestosQuery(): Builder
    {
        return Manifesto::query()
            ->when($this->search, function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->where('title', 'like', "%{$this->search}%")
                        ->orWhere('body', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(Manifesto $manifesto): void
    {
        $manifesto->update([
            'status' => $manifesto->status === PublicationStatus::Published
                ? PublicationStatus::Draft
                : PublicationStatus::Published,
        ]);

        $this->jsToast($manifesto->status === PublicationStatus::Published
            ? "{$manifesto->title} published."
            : "{$manifesto->title} moved to drafts.");
    }

    public function move(Manifesto $manifesto, string $direction): void
    {
        $before = $direction === 'up';

        $neighbor = Manifesto::query()
            ->where(fn (Builder $query) => $query
                ->where('sort_order', $before ? '<' : '>', $manifesto->sort_order)
                ->orWhere(fn (Builder $query) => $query
                    ->where('sort_order', $manifesto->sort_order)
                    ->where('id', $before ? '<' : '>', $manifesto->id)))
            ->orderBy('sort_order', $before ? 'desc' : 'asc')
            ->orderBy('id', $before ? 'desc' : 'asc')
            ->first();

        if (! $neighbor) {
            return;
        }

        [$manifesto->sort_order, $neighbor->sort_order] = [$neighbor->sort_order, $manifesto->sort_order];

        $manifesto->save();
        $neighbor->save();
    }

    public function destroy(Manifesto $manifesto): void
    {
        $title = $manifesto->title;
        $manifesto->delete();

        $this->jsToast("{$title} deleted.");
    }

    public function render(): View
    {
        return view('livewire.manifestos.manifesto-list', [
            'manifestos' => $this->manifestosQuery()->paginate(10),
        ]);
    }
}
