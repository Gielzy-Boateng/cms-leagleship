<?php

use App\Enums\PublicationStatus;
use App\Livewire\Pages\PageForm;
use App\Livewire\Pages\PageList;
use App\Models\Page;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('admin can create a page from blocks', function () {
    Livewire::test(PageForm::class)
        ->set('title', 'Impact')
        ->set('blocks', [
            ['type' => 'heading', 'text' => 'What changed this year', 'level' => 2],
            ['type' => 'paragraph', 'text' => 'Thirty-eight cohorts closed, and two thirds of them were led by a first-time volunteer.'],
            ['type' => 'stat', 'value' => '1,200', 'label' => 'Leaders mentored'],
        ])
        ->set('status', PublicationStatus::Published->value)
        ->call('save')
        ->assertHasNoErrors();

    $page = Page::firstWhere('title', 'Impact');

    expect($page->slug)->toBe('impact')
        ->and($page->status)->toBe(PublicationStatus::Published)
        ->and($page->blocks())->toHaveCount(3)
        ->and($page->blocks()[0])->toMatchArray(['type' => 'heading', 'text' => 'What changed this year', 'level' => 2]);
});

test('admin can set the page slug from the dashboard', function () {
    Livewire::test(PageForm::class)
        ->set('title', 'Impact')
        ->set('slug', 'Impact Report 2026')
        ->set('blocks', [
            ['type' => 'heading', 'text' => 'What changed this year', 'level' => 2],
        ])
        ->set('status', PublicationStatus::Published->value)
        ->call('save')
        ->assertHasNoErrors();

    $page = Page::firstWhere('title', 'Impact');

    expect($page->slug)->toBe('impact-report-2026');

    $this->getJson('/api/pages/impact-report-2026')->assertOk();
});

test('a slug already used by another page is rejected', function () {
    Page::factory()->create(['title' => 'About', 'slug' => 'about']);

    Livewire::test(PageForm::class)
        ->set('title', 'About Us')
        ->set('slug', 'about')
        ->set('blocks', [
            ['type' => 'heading', 'text' => 'Who we are', 'level' => 2],
        ])
        ->call('save')
        ->assertHasErrors(['slug']);

    expect(Page::count())->toBe(1);
});

test('saving a page drops empty blocks', function () {
    Livewire::test(PageForm::class)
        ->set('title', 'Trimmed page')
        ->set('blocks', [
            ['type' => 'heading', 'text' => '   ', 'level' => 2],
            ['type' => 'paragraph', 'text' => '  Kept as is.  '],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $blocks = Page::firstWhere('title', 'Trimmed page')->blocks();

    expect($blocks)->toHaveCount(1)
        ->and($blocks[0])->toMatchArray(['type' => 'paragraph', 'text' => 'Kept as is.']);
});

test('saving a page rejects block types the editor cannot produce', function () {
    Livewire::test(PageForm::class)
        ->set('title', 'Broken page')
        ->set('blocks', [
            ['type' => 'paragraph', 'text' => 'Fine.'],
            ['type' => 'carousel', 'text' => 'Not a block type'],
        ])
        ->call('save')
        ->assertHasErrors(['blocks.1.type']);

    expect(Page::count())->toBe(0);
});

test('a page needs at least one block with content', function () {
    Livewire::test(PageForm::class)
        ->set('title', 'Nothing here')
        ->set('blocks', [['type' => 'heading', 'text' => '', 'level' => 2]])
        ->call('save')
        ->assertHasErrors(['blocks']);

    expect(Page::count())->toBe(0);
});

test('page creation requires a title', function () {
    Livewire::test(PageForm::class)
        ->set('title', '')
        ->call('save')
        ->assertHasErrors(['title']);
});

test('an uploaded block image replaces the one already stored', function () {
    Storage::fake('public');

    $page = Page::factory()->create([
        'slug' => 'founder',
        'content' => [
            ['type' => 'image', 'src' => 'pages/legacy.jpg', 'caption' => 'Opening cohort', 'alt' => 'Volunteers'],
        ],
    ]);

    Storage::disk('public')->put('pages/legacy.jpg', 'previous bytes');

    Livewire::test(PageForm::class, ['page' => $page])
        ->set('blocks', [
            ['type' => 'image', 'id' => 'block-1', 'src' => 'pages/legacy.jpg', 'caption' => 'Opening cohort', 'alt' => 'Volunteers'],
        ])
        ->set('imageUploads.block-1', TemporaryUploadedFile::fake()->image('portrait.jpg'))
        ->call('save')
        ->assertHasNoErrors();

    $src = Page::find($page->id)->blocks()[0]['src'];

    expect($src)->toStartWith('pages/')
        ->and(Storage::disk('public')->exists($src))->toBeTrue()
        ->and(Storage::disk('public')->exists('pages/legacy.jpg'))->toBeFalse();
});

test('removing an image block deletes the file it stored', function () {
    Storage::fake('public');

    $page = Page::factory()->create([
        'slug' => 'about',
        'content' => [
            ['type' => 'image', 'src' => 'pages/keep.jpg', 'caption' => 'Cohort one', 'alt' => 'Graduates'],
            ['type' => 'image', 'src' => 'pages/drop.jpg', 'caption' => 'Cohort two', 'alt' => 'Volunteers'],
        ],
    ]);

    Storage::disk('public')->put('pages/keep.jpg', 'kept bytes');
    Storage::disk('public')->put('pages/drop.jpg', 'dropped bytes');

    Livewire::test(PageForm::class, ['page' => $page])
        ->set('blocks', [
            ['type' => 'image', 'id' => 'block-1', 'src' => 'pages/keep.jpg', 'caption' => 'Cohort one', 'alt' => 'Graduates'],
        ])
        ->call('save')
        ->assertHasNoErrors();

    expect(Page::find($page->id)->imagePaths())->toBe(['pages/keep.jpg'])
        ->and(Storage::disk('public')->exists('pages/keep.jpg'))->toBeTrue()
        ->and(Storage::disk('public')->exists('pages/drop.jpg'))->toBeFalse();
});

test('deleting a page removes the images it stores', function () {
    Storage::fake('public');

    $page = Page::factory()->create([
        'slug' => 'philosophy',
        'content' => [
            ['type' => 'image', 'src' => 'pages/portrait.jpg', 'caption' => 'Founding cohort', 'alt' => 'Founders'],
        ],
    ]);

    Storage::disk('public')->put('pages/portrait.jpg', 'bytes');

    Livewire::test(PageList::class)
        ->call('destroy', $page);

    expect(Page::find($page->id))->toBeNull()
        ->and(Storage::disk('public')->exists('pages/portrait.jpg'))->toBeFalse();
});

test('blocks can be reordered and removed in the editor', function () {
    Livewire::test(PageForm::class)
        ->call('addBlock', 'heading')
        ->call('addBlock', 'paragraph')
        ->call('moveBlock', 2, 'up')
        ->assertSet('blocks.1.type', 'paragraph')
        ->assertSet('blocks.2.type', 'heading')
        ->call('removeBlock', 2)
        ->assertSet('blocks', fn (array $blocks) => count($blocks) === 2);
});

test('the page list publishes and unpublishes', function () {
    $page = Page::factory()->create(['title' => 'Philosophy']);

    Livewire::test(PageList::class)
        ->call('toggleStatus', $page);

    expect($page->fresh()->status)->toBe(PublicationStatus::Published);
});

test('admin can delete a page', function () {
    $page = Page::factory()->create();

    Livewire::test(PageList::class)
        ->call('destroy', $page);

    expect(Page::count())->toBe(0);
});

test('guests cannot reach the page editor', function () {
    auth()->logout();

    $this->get('/pages')->assertRedirect('/login');
    $this->get('/pages/create')->assertRedirect('/login');
});
