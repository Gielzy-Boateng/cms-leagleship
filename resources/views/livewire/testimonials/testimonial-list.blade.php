<div>
<x-page-header title="Testimonials" :breadcrumbs="['Content' => null, 'Testimonials' => null]">
    <x-slot:actions>
        <a href="{{ route('testimonials.create') }}" wire:navigate
           class="inline-flex items-center gap-1.5 rounded-lg bg-gold-500 px-4 py-2 text-sm font-semibold text-navy-900 shadow-sm transition hover:bg-gold-600 hover:text-white">
            <x-icon name="plus" class="text-[16px]" /> New testimonial
        </a>
    </x-slot:actions>
</x-page-header>

<div class="mb-4 max-w-md">
    <div class="relative">
        <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 text-[16px]! -translate-y-1/2 text-gray-400" />
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search testimonials…"
               class="w-full rounded-lg border border-yellow-200 bg-white py-2 pl-9 pr-9 text-sm shadow-sm placeholder:text-gray-400 focus:border-gold-500 focus:outline-none focus:ring-1 focus:ring-gold-500" />
        <x-icon name="progress_activity" wire:loading wire:target="search" style="display: none"
                class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[16px]! animate-spin text-gold-600" />
    </div>
</div>

@if ($testimonials->isEmpty())
    <p class="rounded-2xl border border-dashed border-yellow-300 bg-white px-5 py-10 text-center text-sm text-gray-400">
        No testimonials found. <a href="{{ route('testimonials.create') }}" class="font-medium text-gold-600 hover:underline">Create one</a>.
    </p>
@else
    <x-resource-table :columns="['Order', 'Role', 'Added']">
        @foreach ($testimonials as $testimonial)
            <x-resource-row
                wire:key="testimonial-{{ $testimonial->id }}"
                :item="$testimonial"
                :href="route('testimonials.edit', $testimonial)"
                icon="format_quote"
                :cells="['#'.$testimonial->sort_order, $testimonial->role, $testimonial->created_at?->format('F j, Y')]"
                :blurb="\Illuminate\Support\Str::limit($testimonial->quote, 120)"
            >
                <x-slot:actions>
                    <button wire:click="move({{ $testimonial->id }}, 'up')" title="Move up"
                            wire:loading.attr="disabled" wire:target="move({{ $testimonial->id }}, 'up')"
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-white hover:text-navy-800 disabled:cursor-wait disabled:opacity-50">
                        <x-icon name="arrow-upward" wire:loading.remove wire:target="move({{ $testimonial->id }}, 'up')" class="text-[18px]!" />
                        <x-icon name="progress_activity" wire:loading wire:target="move({{ $testimonial->id }}, 'up')" style="display: none" class="text-[18px]! animate-spin" />
                    </button>
                    <button wire:click="move({{ $testimonial->id }}, 'down')" title="Move down"
                            wire:loading.attr="disabled" wire:target="move({{ $testimonial->id }}, 'down')"
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-white hover:text-navy-800 disabled:cursor-wait disabled:opacity-50">
                        <x-icon name="arrow-downward" wire:loading.remove wire:target="move({{ $testimonial->id }}, 'down')" class="text-[18px]!" />
                        <x-icon name="progress_activity" wire:loading wire:target="move({{ $testimonial->id }}, 'down')" style="display: none" class="text-[18px]! animate-spin" />
                    </button>
                    <button wire:click="toggleStatus({{ $testimonial->id }})" title="{{ $testimonial->status === \App\Enums\PublicationStatus::Published ? 'Unpublish' : 'Publish' }}"
                            wire:loading.attr="disabled" wire:target="toggleStatus({{ $testimonial->id }})"
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-white hover:text-navy-800 disabled:cursor-wait disabled:opacity-50">
                        <x-icon :name="$testimonial->status === \App\Enums\PublicationStatus::Published ? 'eye-slash' : 'eye'" wire:loading.remove wire:target="toggleStatus({{ $testimonial->id }})" class="text-[18px]!" />
                        <x-icon name="progress_activity" wire:loading wire:target="toggleStatus({{ $testimonial->id }})" style="display: none" class="text-[18px]! animate-spin" />
                    </button>
                    <a href="{{ route('testimonials.edit', $testimonial) }}" wire:navigate title="Edit"
                       class="rounded-lg p-1.5 text-gray-400 transition hover:bg-white hover:text-navy-800">
                        <x-icon name="pencil" class="text-[18px]!" />
                    </a>
                    <button wire:click="destroy({{ $testimonial->id }})" wire:confirm="Delete this testimonial?" title="Delete"
                            wire:loading.attr="disabled" wire:target="destroy({{ $testimonial->id }})"
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-white hover:text-red-600 disabled:cursor-wait disabled:opacity-50">
                        <x-icon name="trash" wire:loading.remove wire:target="destroy({{ $testimonial->id }})" class="text-[18px]!" />
                        <x-icon name="progress_activity" wire:loading wire:target="destroy({{ $testimonial->id }})" style="display: none" class="text-[18px]! animate-spin" />
                    </button>
                </x-slot:actions>
            </x-resource-row>
        @endforeach
    </x-resource-table>

    @if ($testimonials->hasPages())
        <div class="mt-6">
            {{ $testimonials->links() }}
        </div>
    @endif
@endif
</div>
