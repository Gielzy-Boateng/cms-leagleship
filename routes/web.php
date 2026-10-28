<?php

use App\Livewire\Auth\Login;
use App\Livewire\Dashboard;
use App\Livewire\Events\EventForm;
use App\Livewire\Events\EventList;
use App\Livewire\Manifestos\ManifestoForm;
use App\Livewire\Manifestos\ManifestoList;
use App\Livewire\Pages\PageForm;
use App\Livewire\Pages\PageList;
use App\Livewire\Programs\ProgramForm;
use App\Livewire\Programs\ProgramList;
use App\Livewire\Settings\SiteSettings;
use App\Livewire\Testimonials\TestimonialForm;
use App\Livewire\Testimonials\TestimonialList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('login', Login::class)->middleware('guest')->name('login');

Route::post('logout', function (Request $request) {
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');

    Route::get('events', EventList::class)->name('events');
    Route::get('events/create', EventForm::class)->name('events.create');
    Route::get('events/{event}/edit', EventForm::class)->name('events.edit');

    Route::get('programs', ProgramList::class)->name('programs');
    Route::get('programs/create', ProgramForm::class)->name('programs.create');
    Route::get('programs/{program}/edit', ProgramForm::class)->name('programs.edit');

    Route::get('manifestos', ManifestoList::class)->name('manifestos');
    Route::get('manifestos/create', ManifestoForm::class)->name('manifestos.create');
    Route::get('manifestos/{manifesto}/edit', ManifestoForm::class)->name('manifestos.edit');

    Route::get('pages', PageList::class)->name('pages');
    Route::get('pages/create', PageForm::class)->name('pages.create');
    Route::get('pages/{page}/edit', PageForm::class)->name('pages.edit');

    Route::get('testimonials', TestimonialList::class)->name('testimonials');
    Route::get('testimonials/create', TestimonialForm::class)->name('testimonials.create');
    Route::get('testimonials/{testimonial}/edit', TestimonialForm::class)->name('testimonials.edit');

    Route::get('settings', SiteSettings::class)->name('settings');
});
