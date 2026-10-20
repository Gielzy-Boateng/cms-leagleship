<?php

namespace App\Livewire\Programs;

use App\Enums\PublicationStatus;
use App\Livewire\Concerns\SendsToasts;
use App\Models\Program;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Programs')]
class ProgramList extends Component
{
    use SendsToasts;
    use WithPagination;

    #[Url]
    public string $search = '';

    /**
     * @return Builder<Program>
     */
    protected function programsQuery(): Builder
    {
        return Program::query()
            ->when($this->search, fn (Builder $query) => $query->where('title', 'like', "%{$this->search}%"))
            ->orderByDesc('id');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(Program $program): void
    {
        $program->update([
            'status' => $program->status === PublicationStatus::Published
                ? PublicationStatus::Draft
                : PublicationStatus::Published,
        ]);

        $this->jsToast($program->status === PublicationStatus::Published
            ? "{$program->title} published."
            : "{$program->title} moved to drafts.");
    }

    public function destroy(Program $program): void
    {
        $title = $program->title;
        $program->delete();

        $this->jsToast("{$title} deleted.");
    }

    public function render(): View
    {
        return view('livewire.programs.program-list', [
            'programs' => $this->programsQuery()->paginate(10),
        ]);
    }
}
