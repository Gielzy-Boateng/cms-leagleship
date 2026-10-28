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

test('admin can create a testimonial from the dashboard', function () {
    Livewire::test(TestimonialForm::class)
        ->set('quote', "The Convergence gave me a stronger sense of belonging.\n\nI left committed to making a positive impact.")
        ->set('name', 'Sarah Cudzo')
        ->set('role', 'Undergraduate, University of Education, Winneba')
        ->set('source', 'The Convergence')
        ->set('status', PublicationStatus::Published->value)
        ->call('save')
        ->assertHasNoErrors();

    $testimonial = Testimonial::firstWhere('name', 'Sarah Cudzo');

    expect($testimonial->status)->toBe(PublicationStatus::Published)
        ->and($testimonial->sort_order)->toBe(1)
        ->and($testimonial->role)->toBe('Undergraduate, University of Education, Winneba')
        ->and($testimonial->quote)->toContain('stronger sense of belonging');
});

test('testimonial creation requires a quote and a name', function () {
    Livewire::test(TestimonialForm::class)
        ->set('quote', '')
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['quote' => 'required', 'name' => 'required']);

    expect(Testimonial::count())->toBe(0);
});

test('admin can attach a portrait to a testimonial', function () {
    Storage::fake('public');

    Livewire::test(TestimonialForm::class)
        ->set('quote', 'It helped me build confidence.')
        ->set('name', 'Ama Boateng')
        ->set('imageUpload', TemporaryUploadedFile::fake()->image('portrait.png'))
        ->set('status', PublicationStatus::Published->value)
        ->call('save')
        ->assertHasNoErrors();

    $testimonial = Testimonial::firstWhere('name', 'Ama Boateng');

    expect(Storage::disk('public')->exists($testimonial->image))->toBeTrue();

    $this->getJson('/api/testimonials')
        ->assertJsonPath('data.0.image', Storage::disk('public')->url($testimonial->image));
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

    $testimonial = Testimonial::factory()->create(['image' => 'testimonials/portrait.webp']);
    Storage::disk('public')->put('testimonials/portrait.webp', 'bytes');

    Livewire::test(TestimonialList::class)->call('toggleStatus', $testimonial);
    expect($testimonial->fresh()->status)->toBe(PublicationStatus::Published);

    Livewire::test(TestimonialList::class)->call('destroy', $testimonial);

    expect(Testimonial::count())->toBe(0)
        ->and(Storage::disk('public')->exists('testimonials/portrait.webp'))->toBeFalse();
});

test('testimonials can be searched by the words they contain', function () {
    Testimonial::factory()->create(['name' => 'Sarah Cudzo', 'quote' => 'A meaningful and transformative experience.']);
    Testimonial::factory()->create(['name' => 'Other Person', 'quote' => 'Something else entirely.']);

    Livewire::test(TestimonialList::class)
        ->set('search', 'transformative')
        ->assertSee('Sarah Cudzo')
        ->assertDontSee('Other Person');
});
