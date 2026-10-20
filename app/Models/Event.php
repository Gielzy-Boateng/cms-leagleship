<?php

namespace App\Models;

use App\Enums\PublicationStatus;
use Database\Factories\EventFactory;
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
 * @property string|null $image
 * @property string|null $location
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property PublicationStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'slug', 'description', 'image', 'location', 'starts_at', 'ends_at', 'status'])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
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
        static::saving(function (self $event): void {
            if (blank($event->slug)) {
                $event->slug = static::uniqueSlug($event->title, $event->id);
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'event';
        $candidate = $base;
        $suffix = 2;

        while (static::where('slug', $candidate)->when($ignoreId, fn (Builder $q) => $q->whereKeyNot($ignoreId))->exists()) {
            $candidate = "{$base}-{$suffix}";
            $suffix++;
        }

        return $candidate;
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
            'image' => $this->imageUrl(),
            'location' => $this->location,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
