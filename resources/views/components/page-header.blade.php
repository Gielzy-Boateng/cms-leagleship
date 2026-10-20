@props(['title', 'breadcrumbs' => [], 'actions' => null])

<header class="mb-6">
    @if ($breadcrumbs !== [])
        <nav class="mb-2 flex items-center gap-1 text-sm text-gray-500" aria-label="Breadcrumb">
            @foreach ($breadcrumbs as $label => $url)
                @if (! $loop->first)
                    <x-icon name="chevron-right" class="text-[14px]! text-gray-400" />
                @endif
                @if ($url)
                    <a href="{{ $url }}" wire:navigate class="hover:text-gray-800">{{ $label }}</a>
                @else
                    <span class="text-gray-700">{{ $label }}</span>
                @endif
            @endforeach
        </nav>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold text-navy-900">{{ $title }}</h1>
        <div class="flex items-center gap-2">
            {{ $actions }}
        </div>
    </div>
</header>
