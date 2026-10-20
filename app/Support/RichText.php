<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class RichText
{
    /**
     * @var array<string, list<string>>
     */
    private const ALLOWED_ELEMENTS = [
        'a' => ['href'],
        'blockquote' => [],
        'br' => [],
        'code' => [],
        'em' => [],
        'h2' => [],
        'h3' => [],
        'li' => [],
        'ol' => [],
        'p' => [],
        'pre' => [],
        's' => [],
        'strong' => [],
        'u' => [],
    ];

    /**
     * Strip anything the rich text editor cannot produce, so stored HTML is safe to render publicly.
     */
    public static function sanitize(string $html): string
    {
        $config = new HtmlSanitizerConfig;

        foreach (self::ALLOWED_ELEMENTS as $element => $attributes) {
            $config = $config->allowElement($element, $attributes);
        }

        return (new HtmlSanitizer($config))->sanitize($html);
    }

    public static function toPlainText(string $html): string
    {
        $text = preg_replace('#<br\s*/?>#i', "\n", $html) ?? $html;
        $text = preg_replace('#</li>#i', "\n", $text) ?? $text;
        $text = preg_replace('#</(?:blockquote|h[1-6]|ol|p|pre|ul)>#i', "\n\n", $text) ?? $text;
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;

        return trim(preg_replace('/\n{3,}/', "\n\n", $text) ?? $text);
    }
}
