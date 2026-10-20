<?php

namespace App\Models;

use App\Enums\PublicationStatus;
use App\Support\PageBlocks;
use App\Support\PageRenderer;
use App\Support\RichText;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property array<int, array<string, mixed>> $content
 * @property PublicationStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'slug', 'content', 'status'])]
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'status' => PublicationStatus::class,
        ];
    }

    /**
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', PublicationStatus::Published);
    }

    protected static function booted(): void
    {
        static::saving(function (self $page): void {
            if (blank($page->slug)) {
                $page->slug = static::uniqueSlug($page->title, $page->id);
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'page';
        $candidate = $base;
        $suffix = 2;

        while (static::where('slug', $candidate)->when($ignoreId, fn (Builder $q) => $q->whereKeyNot($ignoreId))->exists()) {
            $candidate = "{$base}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }

    /**
     * The page content, cleaned of anything the block editor cannot produce.
     *
     * @return list<array<string, mixed>>
     */
    public function blocks(): array
    {
        return PageBlocks::normalize($this->content);
    }

    /**
     * Blocks as the website should consume them: stored image paths resolved to public URLs.
     *
     * @return list<array<string, mixed>>
     */
    public function blocksForApi(): array
    {
        return array_map(fn (array $block): array => $block['type'] === 'image'
            ? [...$block, 'src' => PageRenderer::imageUrl($block['src'])]
            : $block, $this->blocks());
    }

    public function html(): string
    {
        return PageRenderer::toHtml($this->blocks());
    }

    /**
     * Stored paths of every image the page currently references.
     *
     * @return list<string>
     */
    public function imagePaths(): array
    {
        /** @var list<string> $paths */
        $paths = [];

        foreach ($this->blocks() as $block) {
            if ($block['type'] === 'image' && filled($block['src'] ?? null)) {
                $paths[] = (string) $block['src'];
            }
        }

        return $paths;
    }

    /**
     * Remove the images this page stores. Used when the page is deleted.
     */
    public function purgeImages(): void
    {
        $paths = $this->imagePaths();

        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }
    }

    /**
     * First image on the page, used as the cover on dashboard cards.
     */
    public function imageUrl(): ?string
    {
        $path = $this->imagePaths()[0] ?? null;

        return $path === null ? null : PageRenderer::imageUrl($path);
    }

    /**
     * Short plain-text stand-in for the card blurb.
     */
    public function summary(int $words = 22): ?string
    {
        $text = collect($this->blocks())
            ->filter(fn (array $block): bool => in_array($block['type'], ['paragraph', 'quote', 'rich_text'], true))
            ->map(fn (array $block): string => $block['type'] === 'rich_text'
                ? RichText::toPlainText((string) $block['html'])
                : (string) $block['text'])
            ->first(fn (string $value): bool => filled($value));

        return $text ? Str::words($text, $words) : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiResponse(): array
    {
        $blocks = $this->blocks();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'blocks' => $this->blocksForApi(),
            'html' => PageRenderer::toHtml($blocks),
            'body_text' => PageRenderer::toPlainText($blocks),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
