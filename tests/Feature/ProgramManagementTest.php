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
