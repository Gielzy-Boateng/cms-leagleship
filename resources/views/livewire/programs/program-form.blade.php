<div>
<x-page-header :title="$program !== null ? 'Edit program' : 'Create program'"
               :breadcrumbs="['Content' => null, 'Programs' => route('programs'), ($program !== null ? $program->title : 'Create') => null]" />

<form wire:submit="save" class="max-w-3xl space-y-6 rounded-2xl border border-dashed border-yellow-300 bg-white p-6 shadow-sm sm:p-8">
    <div class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="title" class="mb-1.5 block text-sm font-medium text-gray-700">Title</label>
            <input id="title" type="text" wire:model="title" required
                   class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 placeholder:text-gray-400 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
            @error('title') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="slug" class="mb-1.5 block text-sm font-medium text-gray-700">Slug</label>
            <p class="mb-1.5 text-xs text-gray-400">Lowercase letters, numbers and dashes. Leave blank to build it from the title. The website asks for this program at <code class="rounded bg-gray-100 px-1.5 py-0.5 text-gray-600">/api/programs/{{ $slug ?: 'auto-generated-from-title' }}</code></p>
            <input id="slug" type="text" wire:model="slug" placeholder="{{ $program?->slug ?? 'youth-mentorship-program' }}"
                   class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 font-mono text-sm text-navy-800 placeholder:text-gray-400 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
            @error('slug') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="description" class="mb-1.5 block text-sm font-medium text-gray-700">Description</label>
            <textarea id="description" wire:model="description" rows="5"
                      class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 placeholder:text-gray-400 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500"></textarea>
            @error('description') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="sm:col-span-2">
            <span class="mb-1.5 block text-sm font-medium text-gray-700">Tags</span>
            <p class="mb-1.5 text-xs text-gray-400">Shown under the description on the website, for example Courage · Vision · Stewardship. Press Enter or Add after each tag.</p>

            @if ($tags !== [])
                <div class="mb-3 flex flex-wrap gap-2">
                    @foreach ($tags as $index => $tag)
                        <span wire:key="tag-{{ $index }}-{{ $tag }}"
                              class="inline-flex items-center gap-1.5 rounded-full bg-gold-100 px-3 py-1 text-sm font-medium text-gold-800 ring-1 ring-gold-200">
                            {{ $tag }}
                            <button type="button" wire:click="removeTag({{ $index }})"
                                    class="rounded-full p-0.5 text-gold-600 transition hover:bg-gold-200 hover:text-navy-900"
                                    title="Remove {{ $tag }}">
                                <x-icon name="close" class="text-[14px]!" />
                            </button>
                        </span>
                    @endforeach
                </div>
            @endif

            <div class="flex flex-col gap-2 sm:flex-row">
                <input id="tag_input" type="text" wire:model="tagInput" maxlength="40"
                       wire:keydown.enter.prevent="addTag"
                       placeholder="e.g. Courage"
                       class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 placeholder:text-gray-400 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
                <button type="button" wire:click="addTag"
                        class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-lg bg-navy-800 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-navy-700">
                    <x-icon name="plus" class="text-[16px]" /> Add tag
                </button>
            </div>
            @error('tagInput') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            @error('tags') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            @error('tags.*') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror

            <div class="mt-3 flex flex-wrap gap-2">
                @foreach (['Courage', 'Vision', 'Stewardship', 'Excellence', 'Innovation', 'Identity'] as $suggestion)
                    @if (! collect($tags)->contains(fn (string $existing): bool => mb_strtolower($existing) === mb_strtolower($suggestion)))
                        <button type="button" wire:click="addTag('{{ $suggestion }}')"
                                class="rounded-full bg-yellow-50 px-3 py-1 text-xs font-medium text-gray-600 ring-1 ring-yellow-200 transition hover:bg-gold-100 hover:text-gold-800">
                            + {{ $suggestion }}
                        </button>
                    @endif
                @endforeach
            </div>
        </div>

        <div>
            <label for="starts_at" class="mb-1.5 block text-sm font-medium text-gray-700">Date</label>
            <input id="starts_at" type="date" wire:model="startsAt"
                   class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 placeholder:text-gray-400 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
            @error('startsAt') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
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

        <div class="sm:col-span-2 rounded-xl border border-dashed border-yellow-300 bg-yellow-50/60 px-4 py-3">
            <label class="flex items-start gap-3">
                <input type="checkbox" wire:model="isFeatured"
                       class="mt-1 rounded border-yellow-300 text-gold-500 focus:ring-gold-500" />
                <span>
                    <span class="block text-sm font-medium text-gray-700">Featured program</span>
                    <span class="mt-0.5 block text-xs text-gray-400">Show this program in the large Featured Programs section at the top of the Programs page.</span>
                </span>
            </label>
            @error('isFeatured') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="link" class="mb-1.5 block text-sm font-medium text-gray-700">Link (optional)</label>
            <input id="link" type="url" wire:model="link" placeholder="https://…"
                   class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 placeholder:text-gray-400 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
            @error('link') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="sm:col-span-2">
            <span class="mb-1.5 block text-sm font-medium text-gray-700">Cover image</span>
            @if ($imageUpload?->isPreviewable())
                <div class="relative w-full max-w-md">
                    <img src="{{ $imageUpload->temporaryUrl() }}" alt="Cover preview" class="h-44 w-full rounded-xl object-cover ring-1 ring-yellow-200" />
                    <button type="button" wire:click="$set('imageUpload', null)"
                            class="absolute right-2 top-2 rounded-lg bg-navy-900/70 px-2.5 py-1 text-xs font-medium text-white hover:bg-navy-900">Replace</button>
                </div>
            @elseif ($program !== null && $program->image && ! $removeImage)
                <div class="w-full max-w-md">
                    <img src="{{ $program->imageUrl() }}" alt="{{ $program->title }} cover" class="h-44 w-full rounded-xl object-cover ring-1 ring-yellow-200" />
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
        <a href="{{ route('programs') }}" wire:navigate class="text-sm font-medium text-gray-500 hover:text-gray-800">Cancel</a>
        <div class="flex items-center gap-2">
            @if ($program !== null)
                <button type="button" wire:click="delete" wire:confirm="Delete this program?"
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
                <span wire:loading.remove wire:target="save">{{ $program !== null ? 'Save changes' : 'Create program' }}</span>
                <span wire:loading wire:target="save" style="display: none">Saving…</span>
            </button>
        </div>
    </div>
</form>
</div>
