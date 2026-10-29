<?php

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

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

test('guests cannot add a testimonial', function () {
    $this->postJson('/api/testimonials', [
        'quote' => 'The Convergence was a meaningful experience.',
        'name' => 'Sarah Cudzo',
    ])->assertUnauthorized();

    expect(Testimonial::count())->toBe(0);
});

test('admin can add a testimonial card and the public list loads it', function () {
    $admin = User::factory()->create();

    $this->withBasicAuth($admin->email, 'password')
        ->postJson('/api/testimonials', [
            'quote' => "The Convergence was a meaningful and transformative experience.\n\nIt also gave me a stronger sense of belonging and motivation.",
            'name' => 'Sarah Cudzo',
            'role' => 'Undergraduate, University of Education, Winneba',
            'source' => 'The Convergence',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Sarah Cudzo')
        ->assertJsonPath('data.role', 'Undergraduate, University of Education, Winneba')
        ->assertJsonPath('data.source', 'The Convergence')
        ->assertJsonPath('data.quote', "The Convergence was a meaningful and transformative experience.\n\nIt also gave me a stronger sense of belonging and motivation.");

    $this->getJson('/api/testimonials')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Sarah Cudzo')
        ->assertJsonPath('data.0.role', 'Undergraduate, University of Education, Winneba');

    $id = Testimonial::firstWhere('name', 'Sarah Cudzo')->id;

    $this->getJson('/api/testimonials/'.$id)
        ->assertOk()
        ->assertJsonPath('data.name', 'Sarah Cudzo');
});

test('admin can attach a portrait when adding a testimonial', function () {
    Storage::fake('public');

    $admin = User::factory()->create();

    $this->withBasicAuth($admin->email, 'password')
        ->post('/api/testimonials', [
            'quote' => 'It helped me build confidence.',
            'name' => 'Sarah Cudzo',
            'role' => 'Undergraduate, University of Education, Winneba',
            'image' => UploadedFile::fake()->image('portrait.jpg'),
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Sarah Cudzo');

    $testimonial = Testimonial::firstWhere('name', 'Sarah Cudzo');

    expect($testimonial->image)->not->toBeNull()
        ->and(Storage::disk('public')->exists($testimonial->image))->toBeTrue();

    $this->getJson('/api/testimonials')
        ->assertJsonPath('data.0.image', Storage::disk('public')->url($testimonial->image));
});

test('adding a testimonial requires a quote and a name', function () {
    $admin = User::factory()->create();

    $this->withBasicAuth($admin->email, 'password')
        ->postJson('/api/testimonials', [
            'role' => 'Undergraduate, University of Education, Winneba',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['quote', 'name']);

    expect(Testimonial::count())->toBe(0);
});

test('a draft testimonial can be saved without showing on the public list', function () {
    $admin = User::factory()->create();

    $this->withBasicAuth($admin->email, 'password')
        ->postJson('/api/testimonials', [
            'quote' => 'Still checking this quote.',
            'name' => 'Draft Person',
            'status' => 'draft',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Draft Person');

    $this->getJson('/api/testimonials')->assertJsonCount(0, 'data');
});

test('a testimonial portrait must be an image within the size limit', function () {
    $admin = User::factory()->create();

    $this->withBasicAuth($admin->email, 'password')
        ->post('/api/testimonials', [
            'quote' => 'It helped me build confidence.',
            'name' => 'Sarah Cudzo',
            'image' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['image']);

    $this->withBasicAuth($admin->email, 'password')
        ->post('/api/testimonials', [
            'quote' => 'It helped me build confidence.',
            'name' => 'Sarah Cudzo',
            'image' => UploadedFile::fake()->image('portrait.jpg')->size(4097),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['image']);

    expect(Testimonial::count())->toBe(0);
});

test('repeated failed adds are rate limited', function () {
    Cache::flush();

    $payload = [
        'quote' => 'The Convergence was a meaningful experience.',
        'name' => 'Sarah Cudzo',
    ];

    for ($attempt = 0; $attempt < 10; $attempt++) {
        $this->postJson('/api/testimonials', $payload)->assertUnauthorized();
    }

    $this->postJson('/api/testimonials', $payload)->assertTooManyRequests();

    Cache::flush();
});

test('wrong admin credentials cannot add a testimonial', function () {
    $admin = User::factory()->create();

    $this->withBasicAuth($admin->email, 'not-the-password')
        ->postJson('/api/testimonials', [
            'quote' => 'The Convergence was a meaningful experience.',
            'name' => 'Sarah Cudzo',
        ])
        ->assertUnauthorized();

    expect(Testimonial::count())->toBe(0);
});
