<?php

use App\Models\Testimonial;

test('testimonials api returns only published testimonials in display order', function () {
    Testimonial::factory()->published()->create(['name' => 'First Quote', 'sort_order' => 1]);
    Testimonial::factory()->published()->create(['name' => 'Second Quote', 'sort_order' => 2]);
    Testimonial::factory()->create(['name' => 'Secret Quote', 'sort_order' => 3]);

    $response = $this->getJson('/api/testimonials');

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'First Quote')
        ->assertJsonPath('data.1.name', 'Second Quote')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'quote', 'role', 'source', 'image', 'sort_order', 'created_at'],
            ],
        ]);
});

test('testimonials api keeps the quote as it was written', function () {
    Testimonial::factory()->published()->create([
        'quote' => "It helped me gain new knowledge.\n\nIt also gave me a stronger sense of belonging.",
        'role' => 'Undergraduate, University of Education, Winneba',
        'source' => 'The Convergence',
    ]);

    $this->getJson('/api/testimonials')
        ->assertOk()
        ->assertJsonPath('data.0.quote', "It helped me gain new knowledge.\n\nIt also gave me a stronger sense of belonging.")
        ->assertJsonPath('data.0.role', 'Undergraduate, University of Education, Winneba')
        ->assertJsonPath('data.0.source', 'The Convergence');
});

test('testimonials api show returns a published testimonial by id', function () {
    $testimonial = Testimonial::factory()->published()->create();

    $this->getJson('/api/testimonials/'.$testimonial->id)
        ->assertOk()
        ->assertJsonPath('data.id', $testimonial->id);
});

test('testimonials api show hides draft testimonials', function () {
    $testimonial = Testimonial::factory()->create();

    $this->getJson('/api/testimonials/'.$testimonial->id)->assertNotFound();
});
