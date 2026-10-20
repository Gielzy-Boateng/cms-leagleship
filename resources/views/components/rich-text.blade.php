@props(['model', 'value' => ''])

<div wire:ignore
     x-data="richText($wire, @js($model), @js($value))"
     class="overflow-hidden rounded-lg border border-dashed border-yellow-300 bg-white transition focus-within:border-gold-500 focus-within:ring-1 focus-within:ring-gold-500">
    <div class="flex flex-wrap items-center gap-1 border-b border-dashed border-yellow-300 bg-gray-50 px-2 py-1.5">
        <button type="button" wire:key="rich-text-{{ $model }}-undo" title="Undo"
                x-on:mousedown.prevent
                x-on:click="run('undo')"
                :class="canUndo() ? 'text-gray-600 hover:bg-yellow-100' : 'text-gray-300'"
                class="rounded-md p-1.5 transition">
            <x-icon name="undo" class="text-[18px]" />
        </button>
        <button type="button" wire:key="rich-text-{{ $model }}-redo" title="Redo"
                x-on:mousedown.prevent
                x-on:click="run('redo')"
                :class="canRedo() ? 'text-gray-600 hover:bg-yellow-100' : 'text-gray-300'"
                class="rounded-md p-1.5 transition">
            <x-icon name="redo" class="text-[18px]" />
        </button>

        <span wire:key="rich-text-{{ $model }}-divider-1" class="mx-1 h-5 border-l border-yellow-200"></span>

        <select wire:key="rich-text-{{ $model }}-heading" title="Text style"
                x-on:change="setHeading($event.target.value)" :value="currentHeading()"
                class="mr-1 rounded-md border border-yellow-200 bg-white px-1.5 py-1 text-xs font-medium text-gray-600 focus:border-gold-500 focus:outline-none">
            <option value="p">Paragraph</option>
            <option value="h2">Heading 2</option>
            <option value="h3">Heading 3</option>
        </select>

        @foreach ([
            ['icon' => 'format_bold', 'command' => 'toggleBold', 'label' => 'Bold', 'node' => 'bold'],
            ['icon' => 'format_italic', 'command' => 'toggleItalic', 'label' => 'Italic', 'node' => 'italic'],
            ['icon' => 'format_underlined', 'command' => 'toggleUnderline', 'label' => 'Underline', 'node' => 'underline'],
            ['icon' => 'strikethrough', 'command' => 'toggleStrike', 'label' => 'Strikethrough', 'node' => 'strike'],
        ] as $button)
            <button type="button" wire:key="rich-text-{{ $model }}-{{ $button['command'] }}"
                    title="{{ $button['label'] }}"
                    x-on:mousedown.prevent
                    x-on:click="run('{{ $button['command'] }}')"
                    :class="isActive('{{ $button['node'] }}') ? 'bg-navy-900 text-white' : 'text-gray-600 hover:bg-yellow-100'"
                    class="rounded-md p-1.5 transition">
                <x-icon :name="$button['icon']" class="text-[18px]" />
            </button>
        @endforeach

        <span wire:key="rich-text-{{ $model }}-divider-2" class="mx-1 h-5 border-l border-yellow-200"></span>

        @foreach ([
            ['icon' => 'format_list_bulleted', 'command' => 'toggleBulletList', 'label' => 'Bullet list', 'node' => 'bulletList'],
            ['icon' => 'format_list_numbered', 'command' => 'toggleOrderedList', 'label' => 'Numbered list', 'node' => 'orderedList'],
            ['icon' => 'format_quote', 'command' => 'toggleBlockquote', 'label' => 'Quote', 'node' => 'blockquote'],
        ] as $button)
            <button type="button" wire:key="rich-text-{{ $model }}-{{ $button['command'] }}"
                    title="{{ $button['label'] }}"
                    x-on:mousedown.prevent
                    x-on:click="run('{{ $button['command'] }}')"
                    :class="isActive('{{ $button['node'] }}') ? 'bg-navy-900 text-white' : 'text-gray-600 hover:bg-yellow-100'"
                    class="rounded-md p-1.5 transition">
                <x-icon :name="$button['icon']" class="text-[18px]" />
            </button>
        @endforeach

        <span wire:key="rich-text-{{ $model }}-divider-3" class="mx-1 h-5 border-l border-yellow-200"></span>

        <button type="button" wire:key="rich-text-{{ $model }}-link" title="Link"
                x-on:mousedown.prevent
                x-on:click="openLink()"
                :class="isActive('link') ? 'bg-navy-900 text-white' : 'text-gray-600 hover:bg-yellow-100'"
                class="rounded-md p-1.5 transition">
            <x-icon name="link" class="text-[18px]" />
        </button>
        <button type="button" wire:key="rich-text-{{ $model }}-clear" title="Clear formatting"
                x-on:mousedown.prevent
                x-on:click="run('clearNodes'); run('unsetAllMarks')"
                class="rounded-md p-1.5 text-gray-600 transition hover:bg-yellow-100">
            <x-icon name="format_clear" class="text-[18px]" />
        </button>
    </div>

    <div wire:key="rich-text-{{ $model }}-link-bar" x-show="showLink" x-cloak
         class="flex items-center gap-2 border-b border-dashed border-yellow-300 bg-navy-50 px-2 py-1.5">
        <input type="url" placeholder="https://example.com" x-model="linkUrl"
               x-on:keydown.enter.prevent="applyLink()"
               x-on:keydown.escape.prevent="showLink = false"
               class="min-w-0 flex-1 rounded-md border border-yellow-200 bg-white px-2.5 py-1.5 text-sm text-navy-800 focus:border-gold-500 focus:outline-none" />
        <button type="button" x-on:click="applyLink()"
                class="rounded-md bg-gold-500 px-3 py-1.5 text-xs font-semibold text-navy-900 transition hover:bg-gold-600 hover:text-white">Apply</button>
        <button type="button" x-on:click="removeLink()"
                class="rounded-md px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-50">Remove</button>
        <button type="button" x-on:click="showLink = false"
                class="rounded-md p-1.5 text-gray-400 transition hover:bg-yellow-100 hover:text-navy-800">
            <x-icon name="close" class="text-[16px]" />
        </button>
    </div>

    <div x-ref="editable" class="min-h-64 px-3.5 py-3"></div>
</div>
