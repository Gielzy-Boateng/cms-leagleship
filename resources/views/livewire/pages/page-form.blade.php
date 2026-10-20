<div>
<x-page-header :title="$page !== null ? 'Edit page' : 'Create page'"
               :breadcrumbs="['Content' => null, 'Pages' => route('pages'), ($page !== null ? $page->title : 'Create') => null]" />

<form wire:submit="save" class="space-y-6">
    <div class="max-w-3xl rounded-2xl border border-dashed border-yellow-300 bg-white p-6 shadow-sm sm:p-8">
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="title" class="mb-1.5 block text-sm font-medium text-gray-700">Title</label>
                <input id="title" type="text" wire:model="title" required
                       class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 placeholder:text-gray-400 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
                @error('title') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
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

            @if ($page !== null)
                <div class="sm:col-span-2">
                    <p class="text-xs text-gray-400">The website asks for this page at <code class="rounded bg-gray-100 px-1.5 py-0.5 text-gray-600">/api/pages/{{ $page->slug }}</code></p>
                </div>
            @endif
        </div>
    </div>

    @error('blocks') <p class="max-w-3xl text-sm text-red-600">{{ $message }}</p> @enderror

    <div class="space-y-4">
        @foreach ($blocks as $index => $block)
            @php($definition = $blockTypes[$block['type']] ?? ['icon' => 'notes', 'label' => $block['type'], 'fields' => []])
            <div wire:key="block-{{ $block['id'] ?? $index }}"
                 class="rounded-2xl border border-dashed border-yellow-300 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-dashed border-yellow-300 px-5 py-3">
                    <div class="flex items-center gap-2">
                        <x-icon :name="$definition['icon']" class="text-[18px]! text-navy-700" />
                        <span class="text-sm font-semibold text-navy-900">{{ $definition['label'] }}</span>
                        <span class="text-xs text-gray-400">{{ $index + 1 }} of {{ count($blocks) }}</span>
                    </div>

                    <div class="flex items-center gap-0.5">
                        <button type="button" wire:click="moveBlock({{ $index }}, 'up')" title="Move up" @disabled($index === 0)
                                class="rounded-lg p-1.5 text-gray-400 transition hover:bg-yellow-100 hover:text-navy-800 disabled:pointer-events-none disabled:opacity-30">
                            <x-icon name="arrow-upward" class="text-[18px]!" />
                        </button>
                        <button type="button" wire:click="moveBlock({{ $index }}, 'down')" title="Move down" @disabled($index === count($blocks) - 1)
                                class="rounded-lg p-1.5 text-gray-400 transition hover:bg-yellow-100 hover:text-navy-800 disabled:pointer-events-none disabled:opacity-30">
                            <x-icon name="arrow-downward" class="text-[18px]!" />
                        </button>
                        <button type="button" wire:click="removeBlock({{ $index }})" wire:confirm="Remove this block?" title="Remove"
                                class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600">
                            <x-icon name="trash" class="text-[18px]!" />
                        </button>
                    </div>
                </div>

                <div class="space-y-4 px-5 py-4">
                    @foreach ($definition['fields'] as $field => $meta)
                        @php($model = "blocks.{$index}.{$field}")
                        @php($value = $block[$field] ?? '')

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">{{ $meta['label'] }}</label>

                            @if ($meta['kind'] === 'textarea')
                                <textarea wire:model="blocks.{{ $index }}.{{ $field }}" rows="4"
                                          class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 placeholder:text-gray-400 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500"></textarea>
                            @elseif ($meta['kind'] === 'richtext')
                                <x-rich-text :model="$model" :value="$value" />
                            @elseif ($meta['kind'] === 'heading_level')
                                <select wire:model="blocks.{{ $index }}.{{ $field }}"
                                        class="w-full max-w-xs rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500">
                                    <option value="2">Section heading (h2)</option>
                                    <option value="3">Sub heading (h3)</option>
                                </select>
                            @elseif ($meta['kind'] === 'image')
                                @php($pending = $imageUploads[$block['id'] ?? ''] ?? null)

                                @if ($pending?->isPreviewable())
                                    <img src="{{ $pending->temporaryUrl() }}" alt="Pending image preview"
                                         class="mb-3 h-40 w-full max-w-md rounded-xl object-cover ring-1 ring-yellow-200" />
                                @elseif (filled($value))
                                    <img src="{{ \App\Support\PageRenderer::imageUrl($value) }}" alt=""
                                         class="mb-3 h-40 w-full max-w-md rounded-xl object-cover ring-1 ring-yellow-200" />
                                @endif

                                <x-image-upload :model="'imageUploads.'.($block['id'] ?? '')" />
                            @else
                                <input type="text" wire:model="blocks.{{ $index }}.{{ $field }}"
                                       class="w-full rounded-lg border border-yellow-200 bg-yellow-50 px-3.5 py-2.5 text-sm text-navy-800 placeholder:text-gray-400 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500" />
                            @endif

                            @error("blocks.{$index}.{$field}")
                                <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div x-data="{ type: 'paragraph' }"
         class="flex max-w-3xl flex-wrap items-center gap-2 rounded-2xl border border-dashed border-yellow-300 bg-white px-5 py-4 shadow-sm">
        <span class="text-sm font-medium text-gray-700">Add block</span>

        <select x-model="type" aria-label="Block type"
                class="rounded-lg border border-yellow-200 bg-yellow-50 px-3 py-2 text-sm text-navy-800 focus:border-gold-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gold-500">
            @foreach ($blockTypes as $key => $type)
                <option value="{{ $key }}">{{ $type['label'] }}</option>
            @endforeach
        </select>

        <button type="button" x-on:click="$wire.addBlock(type)"
                class="inline-flex items-center gap-1.5 rounded-lg bg-navy-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-navy-700">
            <x-icon name="plus" class="text-[16px]" /> Add
        </button>

        <p class="w-full text-xs text-gray-400 sm:w-auto sm:flex-1">Blocks are published to the website in this order.</p>
    </div>

    <div class="flex items-center justify-between border-t border-dashed border-yellow-300 pt-5">
        <a href="{{ route('pages') }}" wire:navigate class="text-sm font-medium text-gray-500 hover:text-gray-800">Cancel</a>
        <div class="flex items-center gap-2">
            @if ($page !== null)
                <button type="button" wire:click="delete" wire:confirm="Delete this page?"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50">
                    <x-icon name="trash" class="text-[16px]" /> Delete
                </button>
            @endif
            <button type="submit" class="rounded-lg bg-gold-500 px-5 py-2 text-sm font-semibold text-navy-900 shadow-sm transition hover:bg-gold-600 hover:text-white">
                {{ $page !== null ? 'Save changes' : 'Create page' }}
            </button>
        </div>
    </div>
</form>
</div>
