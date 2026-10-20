<?php

use App\Enums\PublicationStatus;
use App\Models\Event;

test('events api returns only published events', function () {
    $published = Event::factory()->published()->create(['title' => 'Public Event', 'slug' => 'public-event']);
    Event::factory()->create(['title' => 'Secret Event', 'slug' => 'secret-event']);

    $response = $this->getJson('/api/events');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Public Event')
        ->assertJsonPath('data.0.slug', 'public-event')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'slug', 'description', 'image', 'location', 'starts_at', 'ends_at', 'created_at'],
            ],
        ]);
});

test('events api show returns a published event by slug', function () {
    $event = Event::factory()->published()->create(['slug' => 'launch-night', 'title' => 'Launch Night']);

    $this->getJson('/api/events/launch-night')
        ->assertOk()
        ->assertJsonPath('data.id', $event->id)
        ->assertJsonPath('data.title', 'Launch Night');
});

test('events api show hides draft events', function () {
    Event::factory()->create(['slug' => 'draft-only']);

    $this->getJson('/api/events/draft-only')->assertNotFound();
});

test('events api upcoming filter excludes past events', function () {
    Event::factory()->published()->create(['slug' => 'past-event', 'starts_at' => now()->subWeek()]);
    Event::factory()->published()->create(['slug' => 'future-event', 'starts_at' => now()->addWeek()]);

    $this->getJson('/api/events?upcoming=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', 'future-event');
});

test('event status casts to publication status enum', function () {
    $event = Event::factory()->create();

    expect($event->fresh()->status)->toBe(PublicationStatus::Draft);
});
