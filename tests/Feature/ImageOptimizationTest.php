<?php

use App\Livewire\Events\EventForm;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
    Storage::fake('public');
});

test('an oversized upload is shrunk to the max edge and stored as webp', function () {
    Livewire::test(EventForm::class)
        ->set('title', 'Rally')
        ->set('imageUpload', TemporaryUploadedFile::fake()->image('huge.jpg', 3000, 2000))
        ->call('save')
        ->assertHasNoErrors();

    $image = Event::firstWhere('title', 'Rally')->image;
    $path = Storage::disk('public')->path($image);

    expect($image)->toEndWith('.webp')
        ->and(exif_imagetype($path))->toBe(IMAGETYPE_WEBP)
        ->and(getimagesize($path)[0])->toBe(1920)
        ->and(getimagesize($path)[1])->toBe(1280);
});

test('a portrait upload keeps its aspect ratio when shrunk', function () {
    Livewire::test(EventForm::class)
        ->set('title', 'Portrait')
        ->set('imageUpload', TemporaryUploadedFile::fake()->image('tall.png', 2000, 3000))
        ->call('save')
        ->assertHasNoErrors();

    $path = Storage::disk('public')->path(Event::firstWhere('title', 'Portrait')->image);

    expect(getimagesize($path)[0])->toBe(1280)
        ->and(getimagesize($path)[1])->toBe(1920);
});

test('a small upload keeps its dimensions but is still re-encoded', function () {
    Livewire::test(EventForm::class)
        ->set('title', 'Small')
        ->set('imageUpload', TemporaryUploadedFile::fake()->image('thumb.jpg', 320, 240))
        ->call('save')
        ->assertHasNoErrors();

    $image = Event::firstWhere('title', 'Small')->image;
    $path = Storage::disk('public')->path($image);

    expect($image)->toEndWith('.webp')
        ->and(getimagesize($path)[0])->toBe(320)
        ->and(getimagesize($path)[1])->toBe(240)
        ->and(filesize($path))->toBeLessThan(200_000);
});
