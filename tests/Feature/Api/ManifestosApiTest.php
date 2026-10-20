<?php

use App\Models\Manifesto;

test('manifestos api returns only published entries in display order', function () {
    Manifesto::factory()->create(['title' => 'Draft Belief', 'sort_order' => 0]);
    Manifesto::factory()->published()->create(['title' => 'Second Belief', 'sort_order' => 2]);
    Manifesto::factory()->published()->create(['title' => 'First Belief', 'sort_order' => 1]);

    $this->getJson('/api/manifestos')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.title', 'First Belief')
        ->assertJsonPath('data.1.title', 'Second Belief')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'slug', 'excerpt', 'body', 'body_text', 'image', 'sort_order', 'created_at'],
            ],
        ]);
});

test('manifestos api returns the html body and a plain text version', function () {
    Manifesto::factory()->published()->create([
        'slug' => 'the-call',
        'body' => '<h2>The Call</h2><p>We <strong>lead</strong> by serving.</p><ul><li>listen</li><li>serve</li></ul>',
    ]);

    $this->getJson('/api/manifestos/the-call')
        ->assertOk()
        ->assertJsonPath('data.body', '<h2>The Call</h2><p>We <strong>lead</strong> by serving.</p><ul><li>listen</li><li>serve</li></ul>')
        ->assertJsonPath('data.body_text', "The Call\n\nWe lead by serving.\n\nlisten\nserve");
});

test('manifestos api show returns a published entry by slug', function () {
    $manifesto = Manifesto::factory()->published()->create(['slug' => 'lead-by-serving', 'title' => 'Lead By Serving']);

    $this->getJson('/api/manifestos/lead-by-serving')
        ->assertOk()
        ->assertJsonPath('data.id', $manifesto->id);
});

test('manifestos api show hides draft entries', function () {
    Manifesto::factory()->create(['slug' => 'draft-belief']);

    $this->getJson('/api/manifestos/draft-belief')->assertNotFound();
});
