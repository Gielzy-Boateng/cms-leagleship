@props(['item', 'href', 'date' => null, 'blurb' => null, 'meta' => null, 'icon' => null])

@php($published = $item->status === \App\Enums\PublicationStatus::Published)

<article {{ $attributes->merge(['class' => 'group relative isolate flex aspect-[16/9] flex-col justify-end overflow-hidden rounded-2xl border border-dashed border-yellow-300 bg-navy-900 shadow-sm transition hover:border-gold-400 hover:shadow-lg']) }}>
    @if ($url = $item->imageUrl())
        <img src="{{ $url }}" alt="" loading="lazy"
             class="pointer-events-none absolute inset-0 h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]" />
    @else
        <x-icon :name="$icon" class="pointer-events-none absolute right-2 bottom-1 text-[88px]! text-white/10" />
    @endif

    <span class="pointer-events-none absolute inset-0 bg-gradient-to-t from-navy-900 via-navy-900/90 to-navy-900/25"></span>

    <a href="{{ $href }}" wire:navigate aria-label="{{ $item->title }}"
       class="absolute inset-0 z-10 rounded-2xl focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-400 focus-visible:ring-offset-2 focus-visible:ring-offset-navy-900"></a>

    <div class="pointer-events-none relative z-20 flex items-end justify-between gap-3 px-5 pt-10 pb-4">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium">
                @if (filled($meta))
                    <span class="text-gold-200">{{ $meta }}</span>
                @endif
                @if (filled($date))
                    <span class="text-white/85">{{ $date }}</span>
                @endif
                <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $published ? 'bg-gold-400 text-navy-900' : 'bg-white/20 text-white ring-1 ring-white/30' }}">{{ $item->status->label() }}</span>
            </div>

            <h3 class="mt-1.5 line-clamp-2 text-xl font-semibold leading-snug text-white group-hover:underline">
                {{ $item->title }}
            </h3>

            @if (filled($blurb))
                <p class="mt-1 line-clamp-2 text-sm text-white/80">{{ $blurb }}</p>
            @endif
        </div>

        @isset($actions)
            <div class="pointer-events-auto relative z-30 flex shrink-0 items-center gap-0.5 self-start">
                {{ $actions }}
            </div>
        @endisset
    </div>
</article>
