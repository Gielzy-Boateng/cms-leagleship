<div>
<x-page-header title="Dashboard" :breadcrumbs="['Dashboard' => null]">
    <x-slot:actions>
        <a href="{{ route('events.create') }}" wire:navigate
           class="inline-flex items-center gap-1.5 rounded-lg bg-gold-500 px-4 py-2 text-sm font-semibold text-navy-900 shadow-sm transition hover:bg-gold-600 hover:text-white">
            <x-icon name="plus" class="text-[16px]" /> New event
        </a>
        <a href="{{ route('programs.create') }}" wire:navigate
           class="inline-flex items-center gap-1.5 rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-yellow-300 transition hover:bg-yellow-100">
            <x-icon name="plus" class="text-[16px]" /> New program
        </a>
    </x-slot:actions>
</x-page-header>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach ([
        ['label' => 'Events', 'total' => $eventCount, 'published' => $publishedEventCount, 'icon' => 'calendar', 'route' => 'events'],
        ['label' => 'Programs', 'total' => $programCount, 'published' => $publishedProgramCount, 'icon' => 'megaphone', 'route' => 'programs'],
        ['label' => 'Manifestos', 'total' => $manifestoCount, 'published' => $publishedManifestoCount, 'icon' => 'menu-book', 'route' => 'manifestos'],
        ['label' => 'Pages', 'total' => $pageCount, 'published' => $publishedPageCount, 'icon' => 'description', 'route' => 'pages'],
        ['label' => 'Testimonials', 'total' => $testimonialCount, 'published' => $publishedTestimonialCount, 'icon' => 'image', 'route' => 'testimonials'],
    ] as $stat)
        <a href="{{ route($stat['route']) }}" wire:navigate
           class="group flex items-start gap-4 rounded-2xl border border-dashed border-yellow-300 bg-white p-5 shadow-sm transition hover:shadow-md">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gray-100 text-navy-700">
                <x-icon :name="$stat['icon']" class="text-[22px]" />
            </span>
            <div>
                <p class="text-sm font-medium text-gray-500">{{ $stat['label'] }}</p>
                <p class="mt-0.5 text-3xl font-bold text-navy-900">{{ $stat['total'] }}</p>
                <p class="text-xs text-gray-400">{{ $stat['published'] }} of {{ $stat['total'] }} published</p>
            </div>
        </a>
    @endforeach
</div>

<div class="mt-8 grid gap-6 xl:grid-cols-2">
    @foreach ([
        ['label' => 'Events', 'items' => $recentEvents, 'resource' => 'events', 'icon' => 'calendar',
            'date' => fn (mixed $item) => $item->starts_at?->format('F j, Y'), 'blurb' => fn (mixed $item) => $item->description],
        ['label' => 'Programs', 'items' => $recentPrograms, 'resource' => 'programs', 'icon' => 'megaphone',
            'date' => fn (mixed $item) => $item->starts_at?->format('F j, Y'), 'blurb' => fn (mixed $item) => $item->description],
        ['label' => 'Manifestos', 'items' => $recentManifestos, 'resource' => 'manifestos', 'icon' => 'menu-book',
            'date' => fn (mixed $item) => $item->created_at?->format('F j, Y'), 'blurb' => fn (mixed $item) => $item->excerpt],
        ['label' => 'Pages', 'items' => $recentPages, 'resource' => 'pages', 'icon' => 'description',
            'date' => fn (mixed $item) => $item->updated_at?->format('F j, Y'), 'blurb' => fn (mixed $item) => $item->summary()],
        ['label' => 'Testimonials', 'items' => $recentTestimonials, 'resource' => 'testimonials', 'icon' => 'image',
            'date' => fn (mixed $item) => $item->created_at?->format('F j, Y'), 'blurb' => fn (mixed $item) => null],
    ] as $section)
        <section>
            <div class="mb-3 flex items-center justify-between gap-3">
                <h2 class="text-base font-semibold text-navy-900">Recent {{ $section['label'] }}</h2>
                <a href="{{ route($section['resource']) }}" wire:navigate class="text-sm font-medium text-gold-600 hover:underline">View all</a>
            </div>

            @if ($section['items']->isEmpty())
                <p class="rounded-2xl border border-dashed border-yellow-300 bg-white px-5 py-6 text-sm text-gray-400">
                    No {{ strtolower($section['label']) }} yet.
                </p>
            @else
                <x-resource-table :columns="['Date']" :compact="true">
                    @foreach ($section['items'] as $item)
                        <x-resource-row
                            wire:key="{{ $section['resource'] }}-{{ $item->id }}"
                            :item="$item"
                            :href="route($section['resource'].'.edit', $item)"
                            :icon="$section['icon']"
                            :cells="[$section['date']($item)]"
                            :blurb="$section['blurb']($item)"
                            :compact="true"
                        />
                    @endforeach
                </x-resource-table>
            @endif
        </section>
    @endforeach
</div>
</div>
