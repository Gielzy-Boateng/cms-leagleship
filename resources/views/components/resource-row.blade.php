@props(['item', 'href', 'cells' => [], 'icon' => null, 'blurb' => null, 'compact' => false])

@php($pad = $compact ? 'px-3 py-2.5' : 'px-4 py-3')
@php($published = $item->status === \App\Enums\PublicationStatus::Published)

<tr {{ $attributes->merge(['class' => 'transition hover:bg-yellow-100']) }}>
    <td class="{{ $pad }}">
        @if ($url = $item->imageUrl())
            <img src="{{ $url }}" alt="" loading="lazy"
                 @class(['rounded-full object-cover ring-1 ring-yellow-200', 'h-9 w-9' => ! $compact, 'h-8 w-8' => $compact]) />
        @else
            <span @class(['flex shrink-0 items-center justify-center rounded-full bg-yellow-100 text-navy-700 ring-1 ring-yellow-200', 'h-9 w-9' => ! $compact, 'h-8 w-8' => $compact])>
                <x-icon :name="$icon" class="text-[16px]!" />
            </span>
        @endif
    </td>

    <td class="{{ $pad }} w-full max-w-0">
        <a href="{{ $href }}" wire:navigate class="block truncate font-medium text-navy-900 hover:text-navy-700 hover:underline">
            {{ $item->title }}
        </a>
        @if (filled($blurb))
            <p class="mt-0.5 truncate text-xs text-gray-500">{{ $blurb }}</p>
        @endif
    </td>

    @foreach ($cells as $cell)
        <td class="{{ $pad }} whitespace-nowrap text-gray-600">{{ $cell ?: '—' }}</td>
    @endforeach

    <td class="{{ $pad }} whitespace-nowrap">
        <span class="inline-block rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $published ? 'bg-gold-400 text-navy-900' : 'bg-yellow-100 text-gray-600 ring-1 ring-yellow-200' }}">
            {{ $item->status->label() }}
        </span>
    </td>

    <td class="{{ $pad }} text-right">
        @isset($actions)
            <span class="inline-flex items-center gap-0.5">{{ $actions }}</span>
        @endisset
    </td>
</tr>
