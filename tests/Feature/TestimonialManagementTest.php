<?php

use App\Enums\PublicationStatus;
use App\Livewire\Testimonials\TestimonialForm;
use App\Livewire\Testimonials\TestimonialList;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('admin can create a testimonial card from the dashboard', function () {
    Storage::fake('public');

    Livewire::test(TestimonialForm::class)
        ->set('imageUploads', [TemporaryUploadedFile::fake()->image('canva-card.png', 1080, 1350)])
        ->set('status', PublicationStatus::Published->value)
        ->call('save')
        ->assertHasNoErrors();

    $testimonial = Testimonial::query()->first();

    expect($testimonial->status)->toBe(PublicationStatus::Published)
        ->and($testimonial->sort_order)->toBe(1)
        ->and(Storage::disk('public')->exists($testimonial->image))->toBeTrue();

    $this->getJson('/api/testimonials')
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.image', Storage::disk('public')->url($testimonial->image));
});

test('admin can batch upload multiple testimonial cards', function () {
    Storage::fake('public');

    Livewire::test(TestimonialForm::class)
        ->set('imageUploads', [
            TemporaryUploadedFile::fake()->image('card-one.jpg', 800, 1000),
            TemporaryUploadedFile::fake()->image('card-two.jpg', 800, 1000),
            TemporaryUploadedFile::fake()->image('card-three.jpg', 800, 1000),
        ])
        ->set('sortOrder', '10')
        ->set('status', PublicationStatus::Published->value)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('testimonials'));

    expect(Testimonial::count())->toBe(3)
        ->and(Testimonial::orderBy('sort_order')->pluck('sort_order')->all())->toBe([10, 11, 12]);

    $this->getJson('/api/testimonials')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('testimonial creation requires at least one card image', function () {
    Livewire::test(TestimonialForm::class)
        ->call('save')
        ->assertHasErrors(['imageUploads']);

    expect(Testimonial::count())->toBe(0);
});

test('admin can replace a card image', function () {
    Storage::fake('public');

    $testimonial = Testimonial::factory()->create(['image' => 'testimonials/old.webp']);
    Storage::disk('public')->put('testimonials/old.webp', 'bytes');

    Livewire::test(TestimonialForm::class, ['testimonial' => $testimonial])
        ->set('imageUpload', TemporaryUploadedFile::fake()->image('new-card.jpg'))
        ->call('save')
        ->assertHasNoErrors();

    $testimonial->refresh();

    expect($testimonial->image)->not->toBe('testimonials/old.webp')
        ->and(Storage::disk('public')->exists($testimonial->image))->toBeTrue()
        ->and(Storage::disk('public')->exists('testimonials/old.webp'))->toBeFalse();
});

test('admin can reorder testimonials', function () {
    $first = Testimonial::factory()->create(['sort_order' => 1]);
    $second = Testimonial::factory()->create(['sort_order' => 2]);

    Livewire::test(TestimonialList::class)->call('move', $second, 'up');

    expect($first->fresh()->sort_order)->toBe(2)
        ->and($second->fresh()->sort_order)->toBe(1);
});

test('admin can toggle and delete a testimonial', function () {
    Storage::fake('public');

    $testimonial = Testimonial::factory()->create(['image' => 'testimonials/card.webp']);
    Storage::disk('public')->put('testimonials/card.webp', 'bytes');

    Livewire::test(TestimonialList::class)->call('toggleStatus', $testimonial);
    expect($testimonial->fresh()->status)->toBe(PublicationStatus::Published);

    Livewire::test(TestimonialList::class)->call('destroy', $testimonial);

    expect(Testimonial::count())->toBe(0)
        ->and(Storage::disk('public')->exists('testimonials/card.webp'))->toBeFalse();
});
