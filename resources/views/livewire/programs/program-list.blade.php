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
               class="w-full rounded-lg border border-yellow-200 bg-white py-2 pl-9 pr-3 text-sm shadow-sm placeholder:text-gray-400 focus:border-gold-500 focus:outline-none focus:ring-1 focus:ring-gold-500" />
    </div>
</div>

@if ($programs->isEmpty())
    <p class="rounded-2xl border border-dashed border-yellow-300 bg-white px-5 py-10 text-center text-sm text-gray-400">
        No programs found. <a href="{{ route('programs.create') }}" class="font-medium text-gold-600 hover:underline">Create one</a>.
    </p>
@else
    <x-resource-table :columns="['Runs from']">
        @foreach ($programs as $program)
            <x-resource-row
                wire:key="program-{{ $program->id }}"
                :item="$program"
                :href="route('programs.edit', $program)"
                icon="megaphone"
                :cells="[$program->starts_at?->format('F j, Y')]"
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
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-white hover:text-navy-800">
                        <x-icon :name="$program->status === \App\Enums\PublicationStatus::Published ? 'eye-slash' : 'eye'" class="text-[18px]!" />
                    </button>
                    <a href="{{ route('programs.edit', $program) }}" wire:navigate title="Edit"
                       class="rounded-lg p-1.5 text-gray-400 transition hover:bg-white hover:text-navy-800">
                        <x-icon name="pencil" class="text-[18px]!" />
                    </a>
                    <button wire:click="destroy({{ $program->id }})" wire:confirm="Delete this program?" title="Delete"
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-white hover:text-red-600">
                        <x-icon name="trash" class="text-[18px]!" />
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
