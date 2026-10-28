<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\Manifesto;
use App\Models\Page;
use App\Models\Program;
use App\Models\Testimonial;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureSidebar();
    }

    /**
     * Provide navigation counters to the dashboard layout.
     */
    protected function configureSidebar(): void
    {
        View::composer('layouts.app', function (\Illuminate\View\View $view): void {
            $view->with('navGroups', [
                'Overview' => [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => ['dashboard'], 'icon' => 'dashboard'],
                ],
                'Content' => [
                    ['label' => 'Events', 'route' => 'events', 'active' => ['events', 'events.*'], 'icon' => 'calendar', 'count' => Event::query()->count()],
                    ['label' => 'Programs', 'route' => 'programs', 'active' => ['programs', 'programs.*'], 'icon' => 'megaphone', 'count' => Program::query()->count()],
                    ['label' => 'Manifestos', 'route' => 'manifestos', 'active' => ['manifestos', 'manifestos.*'], 'icon' => 'menu-book', 'count' => Manifesto::query()->count()],
                    ['label' => 'Pages', 'route' => 'pages', 'active' => ['pages', 'pages.*'], 'icon' => 'description', 'count' => Page::query()->count()],
                    ['label' => 'Testimonials', 'route' => 'testimonials', 'active' => ['testimonials', 'testimonials.*'], 'icon' => 'format_quote', 'count' => Testimonial::query()->count()],
                ],
                'Site' => [
                    ['label' => 'Settings', 'route' => 'settings', 'active' => ['settings'], 'icon' => 'cog'],
                ],
                'Developers' => [
                    ['label' => '/api/events', 'url' => url('/api/events'), 'icon' => 'code-bracket'],
                    ['label' => '/api/programs', 'url' => url('/api/programs'), 'icon' => 'code-bracket'],
                    ['label' => '/api/manifestos', 'url' => url('/api/manifestos'), 'icon' => 'code-bracket'],
                    ['label' => '/api/pages', 'url' => url('/api/pages'), 'icon' => 'code-bracket'],
                    ['label' => '/api/testimonials', 'url' => url('/api/testimonials'), 'icon' => 'code-bracket'],
                    ['label' => '/api/site', 'url' => url('/api/site'), 'icon' => 'code-bracket'],
                ],
            ]);
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
