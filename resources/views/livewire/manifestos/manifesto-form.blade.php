<div>
<x-page-header :title="$manifesto !== null ? 'Edit manifesto entry' : 'Create manifesto entry'"
               :breadcrumbs="['Content' => null, 'Manifestos' => route('manifestos'), ($manifesto !== null ? $manifesto->title : 'Create') => null]" />

<form wire:submit="save" class="max-w-3xl space-y-6 rounded-2xl border border-dashed border-yellow-300 bg-white p-6 shadow-sm sm:p-8">
    <div class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="title" class="mb-1.5 block text-sm font-medium text-gray-700">Title</label>
            <input id="title" type="text" wire:model="title" required
                   class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 placeholder:text-gray-400 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
            @error('title') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="excerpt" class="mb-1.5 block text-sm font-medium text-gray-700">Excerpt (optional)</label>
            <p class="mb-1.5 text-xs text-gray-400">Short summary shown on cards. Falls back to the start of the body when left empty.</p>
            <input id="excerpt" type="text" wire:model="excerpt"
                   class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 placeholder:text-gray-400 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
            @error('excerpt') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="body" class="mb-1.5 block text-sm font-medium text-gray-700">Body</label>
            <p class="mb-1.5 text-xs text-gray-400">Format the section with headings, lists, quotes and links. The website renders this as HTML.</p>
            <x-rich-text model="body" :value="$body" />
            @error('body') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="sort_order" class="mb-1.5 block text-sm font-medium text-gray-700">Display order</label>
            <input id="sort_order" type="number" min="0" step="1" wire:model="sortOrder"
                   class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
            @error('sortOrder') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700">Status</label>
            <select id="status" wire:model="status"
                    class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500">
                @foreach (\App\Enums\PublicationStatus::cases() as $case)
                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                @endforeach
            </select>
            @error('status') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="sm:col-span-2">
            <span class="mb-1.5 block text-sm font-medium text-gray-700">Image (optional)</span>
            @if ($imageUpload?->isPreviewable())
                <div class="relative w-full max-w-md">
                    <img src="{{ $imageUpload->temporaryUrl() }}" alt="Image preview" class="h-44 w-full rounded-xl object-cover ring-1 ring-yellow-200" />
                    <button type="button" wire:click="$set('imageUpload', null)"
                            class="absolute right-2 top-2 rounded-lg bg-navy-900/70 px-2.5 py-1 text-xs font-medium text-white hover:bg-navy-900">Replace</button>
                </div>
            @elseif ($manifesto !== null && $manifesto->image && ! $removeImage)
                <div class="w-full max-w-md">
                    <img src="{{ $manifesto->imageUrl() }}" alt="{{ $manifesto->title }} image" class="h-44 w-full rounded-xl object-cover ring-1 ring-yellow-200" />
                    <label class="mt-2 flex items-center gap-2 text-sm text-gray-500">
                        <input type="checkbox" wire:model="removeImage" class="rounded border-yellow-300" />
                        Remove current image
                    </label>
                </div>
            @else
                <x-image-upload model="imageUpload" />
            @endif
            @error('imageUpload') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="flex items-center justify-between border-t border-dashed border-yellow-300 pt-5">
        <a href="{{ route('manifestos') }}" wire:navigate class="text-sm font-medium text-gray-500 hover:text-gray-800">Cancel</a>
        <div class="flex items-center gap-2">
            @if ($manifesto !== null)
                <button type="button" wire:click="delete" wire:confirm="Delete this manifesto entry?"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50">
                    <x-icon name="trash" class="text-[16px]" /> Delete
                </button>
            @endif
            <button type="submit" class="rounded-lg bg-gold-500 px-5 py-2 text-sm font-semibold text-navy-900 shadow-sm transition hover:bg-gold-600 hover:text-white">
                {{ $manifesto !== null ? 'Save changes' : 'Create entry' }}
            </button>
        </div>
    </div>
</form>
</div>
