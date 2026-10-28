<div>
<x-page-header :title="$testimonial !== null ? 'Edit testimonial' : 'Create testimonial'"
               :breadcrumbs="['Content' => null, 'Testimonials' => route('testimonials'), ($testimonial !== null ? $testimonial->name : 'Create') => null]" />

<form wire:submit="save" class="max-w-3xl space-y-6 rounded-2xl border border-dashed border-yellow-300 bg-white p-6 shadow-sm sm:p-8">
    <div class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="quote" class="mb-1.5 block text-sm font-medium text-gray-700">Quote</label>
            <p class="mb-1.5 text-xs text-gray-400">The words as they were given. Blank lines start a new paragraph on the website.</p>
            <textarea id="quote" wire:model="quote" rows="7" required
                      class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 placeholder:text-gray-400 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500"></textarea>
            @error('quote') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">Name</label>
            <input id="name" type="text" wire:model="name" required
                   class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 placeholder:text-gray-400 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
            @error('name') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="role" class="mb-1.5 block text-sm font-medium text-gray-700">Role (optional)</label>
            <p class="mb-1.5 text-xs text-gray-400">Shown under the name, for example "Undergraduate, University of Education, Winneba".</p>
            <input id="role" type="text" wire:model="role"
                   class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 placeholder:text-gray-400 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
            @error('role') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="source" class="mb-1.5 block text-sm font-medium text-gray-700">Programme or event (optional)</label>
            <p class="mb-1.5 text-xs text-gray-400">Lets the website group quotes by what they are about.</p>
            <input id="source" type="text" wire:model="source"
                   class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 placeholder:text-gray-400 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
            @error('source') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
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
                    class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 placeholder:text-gray-400 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500">
                @foreach (\App\Enums\PublicationStatus::cases() as $case)
                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                @endforeach
            </select>
            @error('status') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="sm:col-span-2">
            <span class="mb-1.5 block text-sm font-medium text-gray-700">Portrait (optional)</span>
            <p class="mb-1.5 text-xs text-gray-400">Shown as the round photo beside the quote. Only publish a portrait the person has agreed to.</p>
            @if ($imageUpload?->isPreviewable())
                <div class="relative w-40">
                    <img src="{{ $imageUpload->temporaryUrl() }}" alt="Portrait preview" class="h-40 w-40 rounded-full object-cover ring-1 ring-yellow-200" />
                    <button type="button" wire:click="$set('imageUpload', null)"
                            class="absolute right-2 top-2 rounded-lg bg-navy-900/70 px-2.5 py-1 text-xs font-medium text-white hover:bg-navy-900">Replace</button>
                </div>
            @elseif ($testimonial !== null && $testimonial->image && ! $removeImage)
                <div class="w-40">
                    <img src="{{ $testimonial->imageUrl() }}" alt="{{ $testimonial->name }} portrait" class="h-40 w-40 rounded-full object-cover ring-1 ring-yellow-200" />
                    <label class="mt-2 flex items-center gap-2 text-sm text-gray-500">
                        <input type="checkbox" wire:model="removeImage" class="rounded border-yellow-300" />
                        Remove current photo
                    </label>
                </div>
            @else
                <x-image-upload model="imageUpload" />
            @endif
            @error('imageUpload') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="flex items-center justify-between border-t border-dashed border-yellow-300 pt-5">
        <a href="{{ route('testimonials') }}" wire:navigate class="text-sm font-medium text-gray-500 hover:text-gray-800">Cancel</a>
        <div class="flex items-center gap-2">
            @if ($testimonial !== null)
                <button type="button" wire:click="delete" wire:confirm="Delete this testimonial?"
                        wire:loading.attr="disabled" wire:target="delete"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 disabled:cursor-wait disabled:opacity-60">
                    <x-icon name="trash" wire:loading.remove wire:target="delete" class="text-[16px]" />
                    <x-icon name="progress_activity" wire:loading wire:target="delete" style="display: none" class="text-[16px]! animate-spin" />
                    <span wire:loading.remove wire:target="delete">Delete</span>
                    <span wire:loading wire:target="delete" style="display: none">Deleting…</span>
                </button>
            @endif
            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                    class="inline-flex items-center gap-2 rounded-lg bg-gold-500 px-5 py-2 text-sm font-semibold text-navy-900 shadow-sm transition hover:bg-gold-600 hover:text-white disabled:cursor-wait disabled:opacity-70">
                <x-icon name="progress_activity" wire:loading wire:target="save" style="display: none" class="text-[16px]! animate-spin" />
                <span wire:loading.remove wire:target="save">{{ $testimonial !== null ? 'Save changes' : 'Create testimonial' }}</span>
                <span wire:loading wire:target="save" style="display: none">Saving…</span>
            </button>
        </div>
    </div>
</form>
</div>
