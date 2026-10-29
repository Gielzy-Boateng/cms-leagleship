<?php

namespace App\Livewire\Testimonials;

use App\Enums\PublicationStatus;
use App\Livewire\Concerns\SendsToasts;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Testimonials')]
class TestimonialList extends Component
{
    use SendsToasts;
    use WithPagination;

    /**
     * @return Builder<Testimonial>
     */
    protected function testimonialsQuery(): Builder
    {
        return Testimonial::query()
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function toggleStatus(Testimonial $testimonial): void
    {
        $testimonial->update([
            'status' => $testimonial->status === PublicationStatus::Published
                ? PublicationStatus::Draft
                : PublicationStatus::Published,
        ]);

        $this->jsToast($testimonial->status === PublicationStatus::Published
            ? "{$testimonial->title} published."
            : "{$testimonial->title} moved to drafts.");
    }

    public function move(Testimonial $testimonial, string $direction): void
    {
        $before = $direction === 'up';

        $neighbor = Testimonial::query()
            ->where(fn (Builder $query) => $query
                ->where('sort_order', $before ? '<' : '>', $testimonial->sort_order)
                ->orWhere(fn (Builder $query) => $query
                    ->where('sort_order', $testimonial->sort_order)
                    ->where('id', $before ? '<' : '>', $testimonial->id)))
            ->orderBy('sort_order', $before ? 'desc' : 'asc')
            ->orderBy('id', $before ? 'desc' : 'asc')
            ->first();

        if (! $neighbor) {
            return;
        }

        [$testimonial->sort_order, $neighbor->sort_order] = [$neighbor->sort_order, $testimonial->sort_order];

        $testimonial->save();
        $neighbor->save();
    }

    public function destroy(Testimonial $testimonial): void
    {
        $label = $testimonial->title;
        $testimonial->purgeImage();
        $testimonial->delete();

        $this->jsToast("{$label} deleted.");
    }

    public function render(): View
    {
        return view('livewire.testimonials.testimonial-list', [
            'testimonials' => $this->testimonialsQuery()->paginate(10),
        ]);
    }
}
