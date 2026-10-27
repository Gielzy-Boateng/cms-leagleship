<?php

namespace App\Models;

use App\Enums\PublicationStatus;
use Database\Factories\ProgramFactory;
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
 * @property string|null $description
 * @property Carbon|null $starts_at
 * @property string|null $image
 * @property string|null $link
 * @property PublicationStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'slug', 'description', 'starts_at', 'image', 'link', 'status'])]
class Program extends Model
{
    /** @use HasFactory<ProgramFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
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
        static::saving(function (self $program): void {
            if (blank($program->slug)) {
                $program->slug = static::uniqueSlug($program->title, $program->id);
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'program';
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
            'description' => $this->description,
            'starts_at' => $this->starts_at?->toDateString(),
            'image' => $this->imageUrl(),
            'link' => $this->link,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
