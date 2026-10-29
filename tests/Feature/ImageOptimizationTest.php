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

test('uploads are stored as-is without server-side re-encoding', function () {
    Livewire::test(EventForm::class)
        ->set('title', 'Rally')
        ->set('imageUpload', TemporaryUploadedFile::fake()->image('huge.jpg', 3000, 2000))
        ->call('save')
        ->assertHasNoErrors();

    $image = Event::firstWhere('title', 'Rally')->image;
    $path = Storage::disk('public')->path($image);

    expect($image)->toEndWith('.jpg')
        ->and(exif_imagetype($path))->toBe(IMAGETYPE_JPEG)
        ->and(getimagesize($path)[0])->toBe(3000)
        ->and(getimagesize($path)[1])->toBe(2000);
});

test('portrait uploads keep their original dimensions', function () {
    Livewire::test(EventForm::class)
        ->set('title', 'Portrait')
        ->set('imageUpload', TemporaryUploadedFile::fake()->image('tall.png', 2000, 3000))
        ->call('save')
        ->assertHasNoErrors();

    $path = Storage::disk('public')->path(Event::firstWhere('title', 'Portrait')->image);

    expect(getimagesize($path)[0])->toBe(2000)
        ->and(getimagesize($path)[1])->toBe(3000);
});

test('a small upload is stored without being rewritten', function () {
    Livewire::test(EventForm::class)
        ->set('title', 'Small')
        ->set('imageUpload', TemporaryUploadedFile::fake()->image('thumb.jpg', 320, 240))
        ->call('save')
        ->assertHasNoErrors();

    $image = Event::firstWhere('title', 'Small')->image;
    $path = Storage::disk('public')->path($image);

    expect($image)->toEndWith('.jpg')
        ->and(getimagesize($path)[0])->toBe(320)
        ->and(getimagesize($path)[1])->toBe(240);
});
