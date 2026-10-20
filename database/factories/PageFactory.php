<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(fake()->sentence(2));

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'content' => [
                ['type' => 'heading', 'text' => Str::title(fake()->sentence(3)), 'level' => 2],
                ['type' => 'paragraph', 'text' => fake()->paragraph()],
                ['type' => 'stat', 'value' => (string) fake()->numberBetween(10, 900), 'label' => Str::title(rtrim(fake()->sentence(2), '.'))],
            ],
            'status' => PublicationStatus::Draft,
        ];
    }

    /**
     * Indicate that the page is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublicationStatus::Published,
        ]);
    }
}
