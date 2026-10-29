<?php

use App\Models\Program;

test('programs api returns only published programs', function () {
    Program::factory()->published()->create(['title' => 'Public Program', 'slug' => 'public-program']);
    Program::factory()->create(['title' => 'Secret Program', 'slug' => 'secret-program']);

    $this->getJson('/api/programs')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Public Program')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'slug', 'description', 'tags', 'starts_at', 'image', 'link', 'is_featured', 'created_at'],
            ],
        ]);
});

test('programs api featured filter returns only featured programs', function () {
    Program::factory()->published()->featured()->create(['title' => 'Featured One', 'slug' => 'featured-one']);
    Program::factory()->published()->create(['title' => 'Regular One', 'slug' => 'regular-one']);

    $this->getJson('/api/programs?featured=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Featured One')
        ->assertJsonPath('data.0.is_featured', true);
});

test('programs api returns tags for the website card', function () {
    Program::factory()->published()->create([
        'slug' => 'vision2535-convergence-cohort-2',
        'tags' => ['Courage', 'Vision', 'Stewardship', 'Excellence', 'Innovation', 'Identity'],
    ]);

    $this->getJson('/api/programs')
        ->assertOk()
        ->assertJsonPath('data.0.tags', [
            'Courage',
            'Vision',
            'Stewardship',
            'Excellence',
            'Innovation',
            'Identity',
        ]);
});

test('programs api show returns a published program by slug', function () {
    $program = Program::factory()->published()->create(['slug' => 'mentorship', 'title' => 'Mentorship']);

    $this->getJson('/api/programs/mentorship')
        ->assertOk()
        ->assertJsonPath('data.id', $program->id);
});

test('programs api show hides draft programs', function () {
    Program::factory()->create(['slug' => 'draft-program']);

    $this->getJson('/api/programs/draft-program')->assertNotFound();
});
