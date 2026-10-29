<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
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
            'description' => fake()->paragraph(),
            'tags' => ($picked = fake()->randomElements(
                ['Courage', 'Vision', 'Stewardship', 'Excellence', 'Innovation', 'Identity'],
                fake()->numberBetween(0, 3),
            )) === [] ? null : $picked,
            'link' => fake()->url(),
            'status' => PublicationStatus::Draft,
            'is_featured' => false,
        ];
    }

    /**
     * Indicate that the program is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublicationStatus::Published,
        ]);
    }

    /**
     * Indicate that the program appears in Featured Programs.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
