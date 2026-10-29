<?php

use App\Enums\PublicationStatus;
use App\Livewire\Programs\ProgramForm;
use App\Livewire\Programs\ProgramList;
use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('admin can create a program from the dashboard', function () {
    Livewire::test(ProgramForm::class)
        ->set('title', 'Scholarship Drive')
        ->set('description', 'Annual scholarship fundraising program')
        ->set('link', 'https://example.com/scholarship')
        ->set('status', PublicationStatus::Published->value)
        ->call('save')
        ->assertHasNoErrors();

    $program = Program::firstWhere('title', 'Scholarship Drive');

    expect($program->slug)->toBe('scholarship-drive')
        ->and($program->status)->toBe(PublicationStatus::Published);
});

test('program creation requires a title', function () {
    Livewire::test(ProgramForm::class)
        ->set('title', '')
        ->call('save')
        ->assertHasErrors(['title' => 'required']);
});

test('admin can set the program slug from the dashboard', function () {
    Livewire::test(ProgramForm::class)
        ->set('title', 'Scholarship Drive')
        ->set('slug', 'Drive 2026')
        ->call('save')
        ->assertHasNoErrors();

    expect(Program::firstWhere('title', 'Scholarship Drive')->slug)->toBe('drive-2026');
});

test('a slug already used by another program is rejected', function () {
    Program::factory()->create(['title' => 'Mentorship Circle', 'slug' => 'mentorship-circle']);

    Livewire::test(ProgramForm::class)
        ->set('title', 'New Circle')
        ->set('slug', 'mentorship-circle')
        ->call('save')
        ->assertHasErrors(['slug']);

    expect(Program::count())->toBe(1);
});

test('clearing the slug field keeps the program on its current slug', function () {
    $program = Program::factory()->create(['title' => 'Original', 'slug' => 'original']);

    Livewire::test(ProgramForm::class, ['program' => $program])
        ->set('slug', '')
        ->call('save')
        ->assertHasNoErrors();

    expect($program->fresh()->slug)->toBe('original');
});

test('admin can mark a program as featured', function () {
    Livewire::test(ProgramForm::class)
        ->set('title', 'Vision2535 Convergence (Cohort 1)')
        ->set('status', PublicationStatus::Published->value)
        ->set('isFeatured', true)
        ->call('save')
        ->assertHasNoErrors();

    $program = Program::firstWhere('title', 'Vision2535 Convergence (Cohort 1)');

    expect($program->is_featured)->toBeTrue();

    $this->getJson('/api/programs?featured=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.is_featured', true);
});

test('admin can add multiple tags to a program', function () {
    Livewire::test(ProgramForm::class)
        ->set('title', 'Vision2535 Convergence (Cohort 2)')
        ->set('status', PublicationStatus::Published->value)
        ->call('addTag', 'Courage')
        ->call('addTag', 'Vision')
        ->set('tagInput', 'Stewardship')
        ->call('addTag')
        ->call('addTag', 'Excellence')
        ->call('addTag', 'Innovation')
        ->call('addTag', 'Identity')
        ->call('save')
        ->assertHasNoErrors();

    $program = Program::firstWhere('title', 'Vision2535 Convergence (Cohort 2)');

    expect($program->tags)->toBe([
        'Courage',
        'Vision',
        'Stewardship',
        'Excellence',
        'Innovation',
        'Identity',
    ]);

    $this->getJson('/api/programs')
        ->assertJsonPath('data.0.tags.0', 'Courage')
        ->assertJsonPath('data.0.tags.5', 'Identity');
});

test('duplicate tags are ignored when adding to a program', function () {
    Livewire::test(ProgramForm::class)
        ->set('title', 'Tagged Program')
        ->call('addTag', 'Courage')
        ->call('addTag', 'courage')
        ->call('save')
        ->assertHasNoErrors();

    expect(Program::firstWhere('title', 'Tagged Program')->tags)->toBe(['Courage']);
});

test('admin can set a date and cover image on a program', function () {
    Storage::fake('public');

    Livewire::test(ProgramForm::class)
        ->set('title', 'Cohort One')
        ->set('startsAt', '2026-01-09')
        ->set('imageUpload', TemporaryUploadedFile::fake()->image('cohort.png'))
        ->set('status', PublicationStatus::Published->value)
        ->call('save')
        ->assertHasNoErrors();

    $program = Program::firstWhere('title', 'Cohort One');

    expect($program->starts_at->toDateString())->toBe('2026-01-09')
        ->and(Storage::disk('public')->exists($program->image))->toBeTrue();

    $this->getJson('/api/programs')
        ->assertJsonPath('data.0.starts_at', '2026-01-09')
        ->assertJsonPath('data.0.image', Storage::disk('public')->url($program->image));
});

test('admin can toggle and delete a program', function () {
    $program = Program::factory()->create();

    Livewire::test(ProgramList::class)->call('toggleStatus', $program);
    expect($program->fresh()->status)->toBe(PublicationStatus::Published);

    Livewire::test(ProgramList::class)->call('destroy', $program);
    expect(Program::count())->toBe(0);
});
