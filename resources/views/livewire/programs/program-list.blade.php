<div>
<x-page-header title="Programs" :breadcrumbs="['Content' => null, 'Programs' => null]">
    <x-slot:actions>
        <a href="{{ route('programs.create') }}" wire:navigate
           class="inline-flex items-center gap-1.5 rounded-lg bg-gold-500 px-4 py-2 text-sm font-semibold text-navy-900 shadow-sm transition hover:bg-gold-600 hover:text-white">
            <x-icon name="plus" class="text-[16px]" /> New program
        </a>
    </x-slot:actions>
</x-page-header>

<div class="mb-4 max-w-md">
    <div class="relative">
        <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 text-[16px]! -translate-y-1/2 text-gray-400" />
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search programs…"
               class="w-full rounded-lg border border-yellow-200 bg-white py-2 pl-9 pr-9 text-sm shadow-sm placeholder:text-gray-400 focus:border-gold-500 focus:outline-none focus:ring-1 focus:ring-gold-500" />
        <x-icon name="progress_activity" wire:loading wire:target="search" style="display: none"
                class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[16px]! animate-spin text-gold-600" />
    </div>
</div>

@if ($programs->isEmpty())
    <p class="rounded-2xl border border-dashed border-yellow-300 bg-white px-5 py-10 text-center text-sm text-gray-400">
        No programs found. <a href="{{ route('programs.create') }}" class="font-medium text-gold-600 hover:underline">Create one</a>.
    </p>
@else
    <x-resource-table :columns="['Runs from', 'Featured']">
        @foreach ($programs as $program)
            <x-resource-row
                wire:key="program-{{ $program->id }}"
                :item="$program"
                :href="route('programs.edit', $program)"
                icon="megaphone"
                :cells="[$program->starts_at?->format('F j, Y'), $program->is_featured ? 'Yes' : '—']"
                :blurb="$program->description"
            >
                <x-slot:actions>
                    @if ($program->link)
                        <a href="{{ $program->link }}" target="_blank" rel="noopener" title="Visit link"
                           class="rounded-lg p-1.5 text-gray-400 transition hover:bg-white hover:text-navy-800">
                            <x-icon name="open_in_new" class="text-[18px]!" />
                        </a>
                    @endif
                    <button wire:click="toggleStatus({{ $program->id }})" title="{{ $program->status === \App\Enums\PublicationStatus::Published ? 'Unpublish' : 'Publish' }}"
                            wire:loading.attr="disabled" wire:target="toggleStatus({{ $program->id }})"
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-white hover:text-navy-800 disabled:cursor-wait disabled:opacity-50">
                        <x-icon :name="$program->status === \App\Enums\PublicationStatus::Published ? 'eye-slash' : 'eye'" wire:loading.remove wire:target="toggleStatus({{ $program->id }})" class="text-[18px]!" />
                        <x-icon name="progress_activity" wire:loading wire:target="toggleStatus({{ $program->id }})" style="display: none" class="text-[18px]! animate-spin" />
                    </button>
                    <a href="{{ route('programs.edit', $program) }}" wire:navigate title="Edit"
                       class="rounded-lg p-1.5 text-gray-400 transition hover:bg-white hover:text-navy-800">
                        <x-icon name="pencil" class="text-[18px]!" />
                    </a>
                    <button wire:click="destroy({{ $program->id }})" wire:confirm="Delete this program?" title="Delete"
                            wire:loading.attr="disabled" wire:target="destroy({{ $program->id }})"
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-white hover:text-red-600 disabled:cursor-wait disabled:opacity-50">
                        <x-icon name="trash" wire:loading.remove wire:target="destroy({{ $program->id }})" class="text-[18px]!" />
                        <x-icon name="progress_activity" wire:loading wire:target="destroy({{ $program->id }})" style="display: none" class="text-[18px]! animate-spin" />
                    </button>
                </x-slot:actions>
            </x-resource-row>
        @endforeach
    </x-resource-table>

    @if ($programs->hasPages())
        <div class="mt-6">
            {{ $programs->links() }}
        </div>
    @endif
@endif
</div>
