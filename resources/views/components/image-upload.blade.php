@props(['model'])

<div x-data="imageUpload($wire, @js($model))" class="max-w-md">
    <input type="file" x-on:change="pick($event.target.files[0])" accept="image/jpeg,image/png,image/webp"
           class="block w-full rounded-lg border border-yellow-200 bg-yellow-50 text-sm text-navy-800 text-gray-500 file:mr-3 file:rounded-l-lg file:border-0 file:bg-navy-800 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-navy-700" />

    <p x-show="busy" x-cloak class="mt-1 flex items-center gap-1.5 text-xs font-medium text-gold-600">
        <x-icon name="progress_activity" class="text-[14px]! animate-spin" /> Preparing image…
    </p>

    <p class="mt-1 text-xs text-gray-400">JPG, PNG or WebP, up to 8 MB. Large files are compressed in your browser before upload so the server stays stable.</p>
</div>
