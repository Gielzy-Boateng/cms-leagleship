<?php

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

test('testimonials api returns only published cards in display order', function () {
    Testimonial::factory()->published()->create(['sort_order' => 1]);
    Testimonial::factory()->published()->create(['sort_order' => 2]);
    Testimonial::factory()->create(['sort_order' => 3]);

    $response = $this->getJson('/api/testimonials');

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.sort_order', 1)
        ->assertJsonPath('data.1.sort_order', 2)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'image', 'sort_order', 'created_at'],
            ],
        ])
        ->assertJsonMissingPath('data.0.quote')
        ->assertJsonMissingPath('data.0.name')
        ->assertJsonMissingPath('data.0.role')
        ->assertJsonMissingPath('data.0.source');
});

test('testimonials api show returns a published card by id', function () {
    $testimonial = Testimonial::factory()->published()->create();

    $this->getJson('/api/testimonials/'.$testimonial->id)
        ->assertOk()
        ->assertJsonPath('data.id', $testimonial->id)
        ->assertJsonPath('data.image', $testimonial->imageUrl());
});

test('testimonials api show hides draft cards', function () {
    $testimonial = Testimonial::factory()->create();

    $this->getJson('/api/testimonials/'.$testimonial->id)->assertNotFound();
});

test('guests cannot add a testimonial', function () {
    Storage::fake('public');

    $this->post('/api/testimonials', [
        'image' => UploadedFile::fake()->image('card.jpg'),
    ])->assertUnauthorized();

    expect(Testimonial::count())->toBe(0);
});

test('admin can add a canva card and the public list loads it', function () {
    Storage::fake('public');

    $admin = User::factory()->create();

    $this->withBasicAuth($admin->email, 'password')
        ->post('/api/testimonials', [
            'image' => UploadedFile::fake()->image('sarah-cudzo-card.jpg', 1080, 1350),
        ])
        ->assertCreated()
        ->assertJsonStructure([
            'data' => ['id', 'image', 'sort_order', 'created_at'],
        ])
        ->assertJsonMissingPath('data.quote')
        ->assertJsonMissingPath('data.name');

    $testimonial = Testimonial::query()->first();

    expect($testimonial)->not->toBeNull()
        ->and(Storage::disk('public')->exists($testimonial->image))->toBeTrue();

    $this->getJson('/api/testimonials')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $testimonial->id)
        ->assertJsonPath('data.0.image', Storage::disk('public')->url($testimonial->image));

    $this->getJson('/api/testimonials/'.$testimonial->id)
        ->assertOk()
        ->assertJsonPath('data.id', $testimonial->id);
});

test('adding a testimonial requires an image', function () {
    $admin = User::factory()->create();

    $this->withBasicAuth($admin->email, 'password')
        ->postJson('/api/testimonials', [
            'sort_order' => 1,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['image']);

    expect(Testimonial::count())->toBe(0);
});

test('a draft card can be saved without showing on the public list', function () {
    Storage::fake('public');

    $admin = User::factory()->create();

    $this->withBasicAuth($admin->email, 'password')
        ->post('/api/testimonials', [
            'image' => UploadedFile::fake()->image('draft-card.jpg'),
            'status' => 'draft',
        ])
        ->assertCreated();

    $this->getJson('/api/testimonials')->assertJsonCount(0, 'data');
});

test('a testimonial card must be an image within the size limit', function () {
    $admin = User::factory()->create();

    $this->withBasicAuth($admin->email, 'password')
        ->post('/api/testimonials', [
            'image' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['image']);

    $this->withBasicAuth($admin->email, 'password')
        ->post('/api/testimonials', [
            'image' => UploadedFile::fake()->image('card.jpg')->size(8193),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['image']);

    expect(Testimonial::count())->toBe(0);
});

test('repeated failed adds are rate limited', function () {
    Cache::flush();

    $payload = ['sort_order' => 1];

    for ($attempt = 0; $attempt < 10; $attempt++) {
        $this->postJson('/api/testimonials', $payload)->assertUnauthorized();
    }

    $this->postJson('/api/testimonials', $payload)->assertTooManyRequests();

    Cache::flush();
});

test('wrong admin credentials cannot add a testimonial', function () {
    Storage::fake('public');

    $admin = User::factory()->create();

    $this->withBasicAuth($admin->email, 'not-the-password')
        ->post('/api/testimonials', [
            'image' => UploadedFile::fake()->image('card.jpg'),
        ])
        ->assertUnauthorized();

    expect(Testimonial::count())->toBe(0);
});
