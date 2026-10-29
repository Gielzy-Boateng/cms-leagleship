<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'image' => 'testimonials/'.fake()->uuid().'.webp',
            'sort_order' => fake()->numberBetween(0, 50),
            'status' => PublicationStatus::Draft,
        ];
    }

    /**
     * Indicate that the testimonial is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublicationStatus::Published,
        ]);
    }
}
