<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(fake()->sentence(3));
        $startsAt = fake()->dateTimeBetween('+1 week', '+3 months');

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'description' => fake()->paragraph(),
            'location' => fake()->city(),
            'starts_at' => Carbon::instance($startsAt),
            'ends_at' => Carbon::instance($startsAt)->addHours(3),
            'status' => PublicationStatus::Draft,
        ];
    }

    /**
     * Indicate that the event is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublicationStatus::Published,
        ]);
    }
}
