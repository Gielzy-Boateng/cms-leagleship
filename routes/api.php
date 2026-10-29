<?php

use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ManifestoController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\ProgramController;
use App\Http\Controllers\Api\SiteSettingController;
use App\Http\Controllers\Api\TestimonialController;
use Illuminate\Support\Facades\Route;

Route::get('events', [EventController::class, 'index']);
Route::get('events/{event}', [EventController::class, 'show']);
Route::get('programs', [ProgramController::class, 'index']);
Route::get('programs/{program}', [ProgramController::class, 'show']);
Route::get('manifestos', [ManifestoController::class, 'index']);
Route::get('manifestos/{manifesto}', [ManifestoController::class, 'show']);
Route::get('pages', [PageController::class, 'index']);
Route::get('pages/{page}', [PageController::class, 'show']);
Route::get('testimonials', [TestimonialController::class, 'index']);
Route::get('testimonials/{testimonial}', [TestimonialController::class, 'show']);
Route::post('testimonials', [TestimonialController::class, 'store'])
    ->middleware(['throttle:10,1', 'auth.basic.once']);
Route::get('site', [SiteSettingController::class, 'show']);
