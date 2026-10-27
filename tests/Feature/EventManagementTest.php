<?php

use App\Enums\PublicationStatus;
use App\Livewire\Events\EventForm;
use App\Livewire\Events\EventList;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('admin can create an event from the dashboard', function () {
    Livewire::test(EventForm::class)
        ->set('title', 'Town Hall')
        ->set('description', 'Quarterly town hall meeting')
        ->set('location', 'Main Hall')
        ->set('startsAt', now()->addWeeks(2)->format('Y-m-d\TH:i'))
        ->set('status', PublicationStatus::Published->value)
        ->call('save')
        ->assertRedirect(route('events.edit', Event::firstWhere('title', 'Town Hall')))
        ->assertHasNoErrors();

    $event = Event::firstWhere('title', 'Town Hall');

    expect($event->slug)->toBe('town-hall')
        ->and($event->status)->toBe(PublicationStatus::Published);
});

test('event creation requires a title', function () {
    Livewire::test(EventForm::class)
        ->set('title', '')
        ->call('save')
        ->assertHasErrors(['title' => 'required']);
});

test('admin can set the event slug from the dashboard', function () {
    Livewire::test(EventForm::class)
        ->set('title', 'Town Hall')
        ->set('slug', ' Annual Town Hall 2026 ')
        ->call('save')
        ->assertHasNoErrors();

    expect(Event::firstWhere('title', 'Town Hall')->slug)->toBe('annual-town-hall-2026');
});

test('a slug already used by another event is rejected', function () {
    Event::factory()->create(['title' => 'Launch Night', 'slug' => 'launch-night']);

    Livewire::test(EventForm::class)
        ->set('title', 'Closing Night')
        ->set('slug', 'launch-night')
        ->call('save')
        ->assertHasErrors(['slug']);

    expect(Event::count())->toBe(1);
});

test('admin can change an event slug to a free one', function () {
    $event = Event::factory()->create(['title' => 'Original', 'slug' => 'original']);

    Livewire::test(EventForm::class, ['event' => $event])
        ->set('slug', 'moved-slug')
        ->call('save')
        ->assertHasNoErrors();

    expect($event->fresh()->slug)->toBe('moved-slug');
});

test('clearing the slug field keeps the event on its current slug', function () {
    $event = Event::factory()->create(['title' => 'Original', 'slug' => 'original']);

    Livewire::test(EventForm::class, ['event' => $event])
        ->set('slug', '')
        ->call('save')
        ->assertHasNoErrors();

    expect($event->fresh()->slug)->toBe('original');
});

test('admin can update an existing event', function () {
    $event = Event::factory()->create(['title' => 'Original', 'slug' => 'original']);

    Livewire::test(EventForm::class, ['event' => $event])
        ->set('title', 'Renamed Event')
        ->call('save')
        ->assertHasNoErrors();

    expect($event->fresh()->title)->toBe('Renamed Event')
        ->and($event->fresh()->slug)->toBe('original');
});

test('admin can toggle event publication from the list', function () {
    $event = Event::factory()->published()->create();

    Livewire::test(EventList::class)
        ->call('toggleStatus', $event);

    expect($event->fresh()->status)->toBe(PublicationStatus::Draft);

    Livewire::test(EventList::class)
        ->call('toggleStatus', $event);

    expect($event->fresh()->status)->toBe(PublicationStatus::Published);
});

test('admin can delete an event from the list', function () {
    $event = Event::factory()->create();

    Livewire::test(EventList::class)
        ->call('destroy', $event);

    expect(Event::count())->toBe(0);
});

test('admin can upload a cover image for an event', function () {
    Storage::fake('public');

    Livewire::test(EventForm::class)
        ->set('title', 'Photo Launch')
        ->set('imageUpload', TemporaryUploadedFile::fake()->image('cover.jpg'))
        ->set('status', PublicationStatus::Published->value)
        ->call('save')
        ->assertHasNoErrors();

    $event = Event::firstWhere('title', 'Photo Launch');

    expect($event->image)->not->toBeNull()
        ->and(Storage::disk('public')->exists($event->image))->toBeTrue();

    $this->getJson('/api/events')
        ->assertJsonPath('data.0.image', Storage::disk('public')->url($event->image));
});

test('cover image rejects non-image files', function () {
    Storage::fake('public');

    Livewire::test(EventForm::class)
        ->set('title', 'Bad Upload')
        ->set('imageUpload', TemporaryUploadedFile::fake()->create('notes.txt'))
        ->call('save')
        ->assertHasErrors(['imageUpload' => 'image']);

    expect(Event::where('title', 'Bad Upload')->exists())->toBeFalse();
});

test('events can be searched in the list', function () {
    Event::factory()->create(['title' => 'Keeta Concert', 'slug' => 'keeta-concert']);
    Event::factory()->create(['title' => 'Other Thing', 'slug' => 'other-thing']);

    Livewire::test(EventList::class)
        ->set('search', 'Keeta')
        ->assertSee('Keeta Concert')
        ->assertDontSee('Other Thing');
});
