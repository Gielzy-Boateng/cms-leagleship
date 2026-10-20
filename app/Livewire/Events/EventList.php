<?php

namespace App\Livewire\Events;

use App\Enums\PublicationStatus;
use App\Livewire\Concerns\SendsToasts;
use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Events')]
class EventList extends Component
{
    use SendsToasts;
    use WithPagination;

    #[Url]
    public string $search = '';

    /**
     * @return Builder<Event>
     */
    protected function eventsQuery(): Builder
    {
        return Event::query()
            ->when($this->search, fn (Builder $query) => $query->where('title', 'like', "%{$this->search}%"))
            ->orderByDesc('starts_at')
            ->orderByDesc('id');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(Event $event): void
    {
        $event->update([
            'status' => $event->status === PublicationStatus::Published
                ? PublicationStatus::Draft
                : PublicationStatus::Published,
        ]);

        $this->jsToast($event->status === PublicationStatus::Published
            ? "{$event->title} published."
            : "{$event->title} moved to drafts.");
    }

    public function destroy(Event $event): void
    {
        $title = $event->title;
        $event->delete();

        $this->jsToast("{$title} deleted.");
    }

    public function render(): View
    {
        return view('livewire.events.event-list', [
            'events' => $this->eventsQuery()->paginate(10),
        ]);
    }
}
