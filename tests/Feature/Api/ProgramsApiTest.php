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
                '*' => ['id', 'title', 'slug', 'description', 'starts_at', 'image', 'link', 'created_at'],
            ],
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
