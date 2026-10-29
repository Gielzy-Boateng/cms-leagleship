<?php

namespace App\Models;

use App\Enums\PublicationStatus;
use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $image
 * @property int $sort_order
 * @property PublicationStatus $status
 * @property-read string $title
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['image', 'sort_order', 'status'])]
class Testimonial extends Model
{
    /** @use HasFactory<TestimonialFactory> */
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

    /**
     * Label for admin lists — the card itself is the image.
     *
     * @return Attribute<non-falsy-string, never>
     */
    protected function title(): Attribute
    {
        return Attribute::get(fn (): string => 'Card #'.$this->sort_order);
    }

    /**
     * Absolute URL of the Canva card image.
     */
    public function imageUrl(): ?string
    {
        return $this->image
            ? Storage::disk('public')->url($this->image)
            : null;
    }

    public function purgeImage(): void
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            Storage::disk('public')->delete($this->image);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'image' => $this->imageUrl(),
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
