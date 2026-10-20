<div>
<x-page-header title="Manifestos" :breadcrumbs="['Content' => null, 'Manifestos' => null]">
    <x-slot:actions>
        <a href="{{ route('manifestos.create') }}" wire:navigate
           class="inline-flex items-center gap-1.5 rounded-lg bg-gold-500 px-4 py-2 text-sm font-semibold text-navy-900 shadow-sm transition hover:bg-gold-600 hover:text-white">
            <x-icon name="plus" class="text-[16px]" /> New entry
        </a>
    </x-slot:actions>
</x-page-header>

<div class="mb-4 max-w-md">
    <div class="relative">
        <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 text-[16px]! -translate-y-1/2 text-gray-400" />
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search manifestos…"
               class="w-full rounded-lg border border-yellow-200 bg-white py-2 pl-9 pr-3 text-sm shadow-sm placeholder:text-gray-400 focus:border-gold-500 focus:outline-none focus:ring-1 focus:ring-gold-500" />
    </div>
</div>

@if ($manifestos->isEmpty())
    <p class="rounded-2xl border border-dashed border-yellow-300 bg-white px-5 py-10 text-center text-sm text-gray-400">
        No manifestos found. <a href="{{ route('manifestos.create') }}" class="font-medium text-gold-600 hover:underline">Create one</a>.
    </p>
@else
    <x-resource-table :columns="['Order', 'Added']">
        @foreach ($manifestos as $manifesto)
            <x-resource-row
                wire:key="manifesto-{{ $manifesto->id }}"
                :item="$manifesto"
                :href="route('manifestos.edit', $manifesto)"
                icon="menu-book"
                :cells="['#'.$manifesto->sort_order, $manifesto->created_at?->format('F j, Y')]"
                :blurb="$manifesto->excerpt ?? \Illuminate\Support\Str::limit(\App\Support\RichText::toPlainText($manifesto->body), 120)"
            >
                <x-slot:actions>
                    <button wire:click="move({{ $manifesto->id }}, 'up')" title="Move up"
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-white hover:text-navy-800">
                        <x-icon name="arrow-upward" class="text-[18px]!" />
                    </button>
                    <button wire:click="move({{ $manifesto->id }}, 'down')" title="Move down"
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-white hover:text-navy-800">
                        <x-icon name="arrow-downward" class="text-[18px]!" />
                    </button>
                    <button wire:click="toggleStatus({{ $manifesto->id }})" title="{{ $manifesto->status === \App\Enums\PublicationStatus::Published ? 'Unpublish' : 'Publish' }}"
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-white hover:text-navy-800">
                        <x-icon :name="$manifesto->status === \App\Enums\PublicationStatus::Published ? 'eye-slash' : 'eye'" class="text-[18px]!" />
                    </button>
                    <a href="{{ route('manifestos.edit', $manifesto) }}" wire:navigate title="Edit"
                       class="rounded-lg p-1.5 text-gray-400 transition hover:bg-white hover:text-navy-800">
                        <x-icon name="pencil" class="text-[18px]!" />
                    </a>
                    <button wire:click="destroy({{ $manifesto->id }})" wire:confirm="Delete this manifesto entry?" title="Delete"
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-white hover:text-red-600">
                        <x-icon name="trash" class="text-[18px]!" />
                    </button>
                </x-slot:actions>
            </x-resource-row>
        @endforeach
    </x-resource-table>

    @if ($manifestos->hasPages())
        <div class="mt-6">
            {{ $manifestos->links() }}
        </div>
    @endif
@endif
</div>
