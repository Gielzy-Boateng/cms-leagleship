<?php

namespace App\Models;

use App\Enums\PublicationStatus;
use App\Support\RichText;
use Database\Factories\ManifestoFactory;
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
 * @property string|null $excerpt
 * @property string $body
 * @property string|null $image
 * @property int $sort_order
 * @property PublicationStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'slug', 'excerpt', 'body', 'image', 'sort_order', 'status'])]
class Manifesto extends Model
{
    /** @use HasFactory<ManifestoFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
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
        static::saving(function (self $manifesto): void {
            if (blank($manifesto->slug)) {
                $manifesto->slug = static::uniqueSlug($manifesto->title, $manifesto->id);
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'manifesto';
        $candidate = $base;
        $suffix = 2;

        while (static::slugTaken($candidate, $ignoreId)) {
            $candidate = "{$base}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }

    /**
     * Whether the slug is already used by another record.
     */
    public static function slugTaken(string $slug, ?int $ignoreId = null): bool
    {
        return static::where('slug', $slug)
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    /**
     * Absolute URL of the cover image, or null when none is set.
     */
    public function imageUrl(): ?string
    {
        return $this->image
            ? Storage::disk('public')->url($this->image)
            : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'body_text' => RichText::toPlainText($this->body),
            'image' => $this->imageUrl(),
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
