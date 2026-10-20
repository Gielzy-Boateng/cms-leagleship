<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Models\Manifesto;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Manifesto>
 */
class ManifestoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(fake()->sentence(3));

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'excerpt' => fake()->sentence(12),
            'body' => fake()->paragraphs(3, true),
            'sort_order' => fake()->numberBetween(0, 50),
            'status' => PublicationStatus::Draft,
        ];
    }

    /**
     * Indicate that the manifesto is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublicationStatus::Published,
        ]);
    }
}
