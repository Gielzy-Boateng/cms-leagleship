<?php

namespace App\Support;

use Illuminate\Validation\Rule;

/**
 * Canonical definition of the page block types the editor, the validator and the renderer share.
 */
class PageBlocks
{
    /**
     * @var array<string, array{
     *     icon: string,
     *     label: string,
     *     fields: array<string, array{label: string, kind: string}>
     * }>
     */
    public const TYPES = [
        'heading' => [
            'icon' => 'title',
            'label' => 'Heading',
            'fields' => [
                'text' => ['label' => 'Text', 'kind' => 'text'],
                'level' => ['label' => 'Level', 'kind' => 'heading_level'],
            ],
        ],
        'paragraph' => [
            'icon' => 'notes',
            'label' => 'Paragraph',
            'fields' => [
                'text' => ['label' => 'Text', 'kind' => 'textarea'],
            ],
        ],
        'rich_text' => [
            'icon' => 'draw',
            'label' => 'Rich text',
            'fields' => [
                'html' => ['label' => 'Content', 'kind' => 'richtext'],
            ],
        ],
        'image' => [
            'icon' => 'image',
            'label' => 'Image',
            'fields' => [
                'src' => ['label' => 'Image', 'kind' => 'image'],
                'caption' => ['label' => 'Caption', 'kind' => 'text'],
                'alt' => ['label' => 'Alt text', 'kind' => 'text'],
            ],
        ],
        'quote' => [
            'icon' => 'format_quote',
            'label' => 'Quote',
            'fields' => [
                'text' => ['label' => 'Quote', 'kind' => 'textarea'],
                'attribution' => ['label' => 'Attribution', 'kind' => 'text'],
            ],
        ],
        'stat' => [
            'icon' => 'query_stats',
            'label' => 'Stat',
            'fields' => [
                'value' => ['label' => 'Value', 'kind' => 'text'],
                'label' => ['label' => 'Label', 'kind' => 'text'],
            ],
        ],
    ];

    /**
     * Drop unknown types and unknown fields, and discard blocks that carry no content.
     *
     * @return list<array<string, mixed>>
     */
    public static function normalize(mixed $blocks): array
    {
        $clean = [];

        foreach (is_array($blocks) ? $blocks : [] as $block) {
            $type = is_array($block) ? ($block['type'] ?? null) : null;

            if (! is_string($type) || ! isset(self::TYPES[$type])) {
                continue;
            }

            $entry = ['type' => $type];
            $hasContent = false;

            foreach (self::TYPES[$type]['fields'] as $name => $field) {
                $value = $block[$name] ?? null;

                if ($field['kind'] === 'heading_level') {
                    $entry[$name] = in_array((int) $value, [2, 3], true) ? (int) $value : 2;

                    continue;
                }

                $value = trim((string) (is_scalar($value) ? $value : ''));

                if ($value !== '') {
                    $hasContent = true;
                }

                $entry[$name] = $value;
            }

            if ($hasContent) {
                $clean[] = $entry;
            }
        }

        return $clean;
    }

    /**
     * An empty block of the given type, ready to be edited.
     *
     * @return array<string, mixed>
     */
    public static function blank(string $type): array
    {
        $entry = ['type' => isset(self::TYPES[$type]) ? $type : 'paragraph'];

        foreach (self::TYPES[$entry['type']]['fields'] as $name => $field) {
            $entry[$name] = $field['kind'] === 'heading_level' ? 2 : '';
        }

        return $entry;
    }

    /**
     * Validation rules for a block array held on the given component property.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rules(string $property = 'blocks'): array
    {
        $max = ['text' => 1000, 'textarea' => 20000, 'richtext' => 100000, 'image' => 255];

        $rules = [
            $property => ['required', 'array'],
            "{$property}.*.type" => ['required', 'string', Rule::in(array_keys(self::TYPES))],
        ];

        foreach (self::TYPES as $type) {
            foreach ($type['fields'] as $name => $field) {
                if (! isset($max[$field['kind']])) {
                    continue;
                }

                $rules["{$property}.*.{$name}"] = ['nullable', 'string', 'max:'.$max[$field['kind']]];
            }
        }

        return $rules;
    }
}
