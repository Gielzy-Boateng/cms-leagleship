<div class="w-full max-w-md overflow-hidden rounded-2xl border border-dashed border-yellow-300 bg-white shadow-sm">
    <div class="bg-navy-900 px-8 py-7">
        <img src="{{ asset('img/logo-white.webp') }}" alt="Diba Leagleship Institute" class="h-14 w-auto" />
    </div>

    <div class="p-8">
        <h1 class="text-xl font-semibold text-navy-900">Leadership CMS</h1>
        <p class="mt-1 text-sm text-gray-500">Sign in to manage events and programs published to the website.</p>

        <form wire:submit="login" class="mt-7 space-y-5">
            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700">Email</label>
                <input id="email" type="email" wire:model="email" autocomplete="username" required
                       class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
                @error('email') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700">Password</label>
                <input id="password" type="password" wire:model="password" autocomplete="current-password" required
                       class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
                @error('password') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-500">
                <input type="checkbox" wire:model="remember" class="rounded border-yellow-300" />
                Remember me
            </label>

            <button type="submit" wire:loading.attr="disabled" wire:target="login"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-gold-500 px-4 py-2.5 text-sm font-semibold text-navy-900 shadow-sm transition hover:bg-gold-600 hover:text-white disabled:cursor-wait disabled:opacity-70">
                <x-icon name="progress_activity" wire:loading wire:target="login" style="display: none" class="text-[16px]! animate-spin" />
                <span wire:loading.remove wire:target="login">Sign in</span>
                <span wire:loading wire:target="login" style="display: none">Signing in…</span>
            </button>
        </form>
    </div>
</div>
