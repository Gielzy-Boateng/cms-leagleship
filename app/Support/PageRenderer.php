<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Turns normalised page blocks into the semantic HTML the website can render directly.
 */
class PageRenderer
{
    /**
     * @param  list<array<string, mixed>>  $blocks
     */
    public static function toHtml(array $blocks): string
    {
        $html = [];

        foreach ($blocks as $block) {
            $rendered = match ($block['type']) {
                'heading' => self::heading($block),
                'paragraph' => '<p>'.nl2br(e($block['text']), false).'</p>',
                'rich_text' => RichText::sanitize((string) $block['html']),
                'image' => self::image($block),
                'quote' => self::quote($block),
                'stat' => '<div class="stat"><strong>'.e($block['value']).'</strong><span>'.e($block['label']).'</span></div>',
                default => '',
            };

            if ($rendered !== '') {
                $html[] = $rendered;
            }
        }

        return implode("\n", $html);
    }

    /**
     * @param  list<array<string, mixed>>  $blocks
     */
    public static function toPlainText(array $blocks): string
    {
        $text = [];

        foreach ($blocks as $block) {
            $text[] = match ($block['type']) {
                'rich_text' => RichText::toPlainText((string) $block['html']),
                'image' => trim(($block['caption'] ?? '').' '.($block['alt'] ?? '')),
                'stat' => trim(($block['value'] ?? '').' '.($block['label'] ?? '')),
                'quote' => trim(($block['text'] ?? '').' - '.($block['attribution'] ?? '')),
                default => (string) ($block['text'] ?? ''),
            };
        }

        return trim(preg_replace('/(\R){3,}/', "\n\n", implode("\n\n", array_filter($text))) ?? '');
    }

    public static function imageUrl(?string $path): ?string
    {
        return filled($path) ? Storage::disk('public')->url($path) : null;
    }

    /**
     * @param  array<string, mixed>  $block
     */
    protected static function heading(array $block): string
    {
        $level = in_array((int) ($block['level'] ?? 2), [2, 3], true) ? (int) $block['level'] : 2;

        return filled($block['text']) ? "<h{$level}>".e($block['text'])."</h{$level}>" : '';
    }

    /**
     * @param  array<string, mixed>  $block
     */
    protected static function image(array $block): string
    {
        $url = self::imageUrl($block['src'] ?? null);

        if ($url === null) {
            return '';
        }

        $html = '<figure><img src="'.e($url).'" alt="'.e($block['alt'] ?? '').'" loading="lazy" />';

        if (filled($block['caption'] ?? null)) {
            $html .= '<figcaption>'.e($block['caption']).'</figcaption>';
        }

        return $html.'</figure>';
    }

    /**
     * @param  array<string, mixed>  $block
     */
    protected static function quote(array $block): string
    {
        if (blank($block['text'] ?? null)) {
            return '';
        }

        $html = '<blockquote><p>'.nl2br(e($block['text']), false).'</p>';

        if (filled($block['attribution'] ?? null)) {
            $html .= '<cite>'.e($block['attribution']).'</cite>';
        }

        return $html.'</blockquote>';
    }
}
