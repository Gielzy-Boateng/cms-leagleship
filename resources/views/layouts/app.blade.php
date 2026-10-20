<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Diba Leagleship') }} · Leadership CMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen font-sans text-gray-800 antialiased">
    <div class="flex min-h-screen flex-col gap-4 p-4 lg:flex-row lg:p-6">
        <aside class="shrink-0 lg:sticky lg:top-6 lg:h-[calc(100vh-3rem)] lg:w-72">
            <div class="flex h-full flex-col overflow-hidden rounded-2xl border border-dashed border-yellow-300 bg-white shadow-sm">
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center bg-navy-900 px-5 py-5">
                    <img src="{{ asset('img/logo-white.webp') }}" alt="Diba Leagleship Institute" class="h-12 w-auto" />
                </a>

                <div class="flex flex-1 flex-col p-5">
                    <form action="{{ route('events') }}" method="GET" class="relative">
                        <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[18px]! text-gray-400" />
                        <input type="search" name="search" placeholder="Search events…"
                               class="w-full rounded-lg border border-yellow-200 bg-yellow-50 py-2 pl-10 pr-3 text-sm text-navy-800 placeholder:text-gray-400 focus:border-gold-500 focus:outline-none focus:ring-1 focus:ring-gold-500" />
                    </form>

                    <nav class="mt-6 min-h-0 flex-1 space-y-6 overflow-y-auto">
                        @foreach ($navGroups as $group => $items)
                            <div>
                                <p class="mb-2 px-3 text-xs font-medium uppercase tracking-wider text-gray-400">{{ $group }}</p>
                                <div class="space-y-1">
                                    @foreach ($items as $item)
                                        <a href="{{ isset($item['route']) ? route($item['route']) : $item['url'] }}"
                                           @if (isset($item['route'])) wire:navigate @else target="_blank" rel="noopener" @endif
                                           @class([
                                               'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                                               'bg-navy-900 text-white' => isset($item['route']) && request()->routeIs(...$item['active']),
                                               'text-gray-600 hover:bg-yellow-100 hover:text-navy-900' => ! (isset($item['route']) && request()->routeIs(...$item['active'])),
                                           ])>
                                            <x-icon :name="$item['icon']" class="text-[18px]" />
                                            <span class="flex-1">{{ $item['label'] }}</span>
                                            @if (isset($item['count']))
                                                <span @class([
                                                    'rounded-full px-2 py-0.5 text-xs font-semibold',
                                                    'bg-gold-500 text-navy-900' => request()->routeIs(...$item['active']),
                                                    'bg-yellow-100 text-gold-800' => ! request()->routeIs(...$item['active']),
                                                ])>{{ $item['count'] }}</span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </nav>

                    <div class="mt-6 flex items-center gap-3 border-t border-dashed border-yellow-300 pt-4">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gold-500 text-xs font-semibold text-navy-900">
                            {{ auth()->user()->initials() }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-navy-900">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-gray-400">{{ auth()->user()->email }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" title="Log out"
                                    class="rounded-lg p-2 text-gray-400 transition hover:bg-yellow-100 hover:text-navy-800">
                                <x-icon name="logout" class="text-[18px]" />
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <main class="min-w-0 flex-1">
            {{ $slot }}
        </main>
    </div>

    <x-toasts :message="session('status')" />

    @livewireScripts
</body>
</html>
