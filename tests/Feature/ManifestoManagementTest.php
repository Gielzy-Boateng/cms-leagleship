<?php

use App\Enums\PublicationStatus;
use App\Livewire\Manifestos\ManifestoForm;
use App\Livewire\Manifestos\ManifestoList;
use App\Models\Manifesto;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('admin can create a manifesto entry from the dashboard', function () {
    Livewire::test(ManifestoForm::class)
        ->set('title', 'We lead by serving')
        ->set('excerpt', 'Leadership is measured by what it gives away.')
        ->set('body', '<p>Every programme starts from one question.</p><p>We would rather walk beside a community than in front of it.</p>')
        ->set('status', PublicationStatus::Published->value)
        ->call('save')
        ->assertHasNoErrors();

    $manifesto = Manifesto::firstWhere('title', 'We lead by serving');

    expect($manifesto->slug)->toBe('we-lead-by-serving')
        ->and($manifesto->status)->toBe(PublicationStatus::Published)
        ->and($manifesto->sort_order)->toBe(1)
        ->and($manifesto->body)->toContain('<p>Every programme starts from one question.</p>');
});

test('saving a manifesto entry sanitises the rich text body', function () {
    Livewire::test(ManifestoForm::class)
        ->set('title', 'The Leagle Creed')
        ->set('body', '<p>We lead.</p><script>alert("boom")</script><img src="x" onerror="steal()"><a href="https://example.com" onclick="track()">read the creed</a><a href="/events">see what is next</a>')
        ->call('save')
        ->assertHasNoErrors();

    $body = Manifesto::firstWhere('title', 'The Leagle Creed')->body;

    expect($body)->not->toContain('script')
        ->and($body)->not->toContain('onerror')
        ->and($body)->not->toContain('onclick')
        ->and($body)->toContain('<p>We lead.</p>')
        ->and($body)->toContain('<a href="https://example.com">read the creed</a>')
        ->and($body)->toContain('<a href="/events">see what is next</a>');
});

test('saving a manifesto entry requires visible text in the body', function () {
    Livewire::test(ManifestoForm::class)
        ->set('title', 'Empty section')
        ->set('body', '<p></p>')
        ->call('save')
        ->assertHasErrors(['body']);

    expect(Manifesto::count())->toBe(0);
});

test('manifesto creation requires a title and body', function () {
    Livewire::test(ManifestoForm::class)
        ->set('title', '')
        ->set('body', '')
        ->call('save')
        ->assertHasErrors(['title' => 'required', 'body' => 'required']);
});

test('admin can attach an image to a manifesto entry', function () {
    Storage::fake('public');

    Livewire::test(ManifestoForm::class)
        ->set('title', 'Promise over performance')
        ->set('body', 'We publish our commitments.')
        ->set('imageUpload', TemporaryUploadedFile::fake()->image('belief.png'))
        ->set('status', PublicationStatus::Published->value)
        ->call('save')
        ->assertHasNoErrors();

    $manifesto = Manifesto::firstWhere('title', 'Promise over performance');

    expect(Storage::disk('public')->exists($manifesto->image))->toBeTrue();

    $this->getJson('/api/manifestos')
        ->assertJsonPath('data.0.image', Storage::disk('public')->url($manifesto->image));
});

test('admin can reorder manifesto entries', function () {
    $first = Manifesto::factory()->create(['sort_order' => 1]);
    $second = Manifesto::factory()->create(['sort_order' => 2]);

    Livewire::test(ManifestoList::class)->call('move', $second, 'up');

    expect($first->fresh()->sort_order)->toBe(2)
        ->and($second->fresh()->sort_order)->toBe(1);

    Livewire::test(ManifestoList::class)->call('move', $first, 'down');

    expect($first->fresh()->sort_order)->toBe(2);
});

test('admin can toggle and delete a manifesto entry', function () {
    $manifesto = Manifesto::factory()->create();

    Livewire::test(ManifestoList::class)->call('toggleStatus', $manifesto);
    expect($manifesto->fresh()->status)->toBe(PublicationStatus::Published);

    Livewire::test(ManifestoList::class)->call('destroy', $manifesto);
    expect(Manifesto::count())->toBe(0);
});
