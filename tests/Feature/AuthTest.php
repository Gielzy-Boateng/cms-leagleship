<?php

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('guests are redirected from the dashboard to login', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('admin can log in and reach the dashboard', function () {
    $user = User::factory()->create([
        'email' => 'admin@cms.local',
        'password' => Hash::make('password'),
    ]);

    Livewire::test(Login::class)
        ->set('email', 'admin@cms.local')
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect(route('dashboard'));

    expect(auth()->id())->toBe($user->id);
});

test('invalid credentials are rejected', function () {
    User::factory()->create([
        'email' => 'admin@cms.local',
        'password' => Hash::make('password'),
    ]);

    Livewire::test(Login::class)
        ->set('email', 'admin@cms.local')
        ->set('password', 'wrong-password')
        ->call('login')
        ->assertHasErrors('email');

    expect(auth()->check())->toBeFalse();
});

test('logout ends the session', function () {
    $this->actingAs(User::factory()->create())
        ->post('/logout')
        ->assertRedirect('/login');

    expect(auth()->check())->toBeFalse();
});
