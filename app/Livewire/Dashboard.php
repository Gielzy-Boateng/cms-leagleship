<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\Manifesto;
use App\Models\Page;
use App\Models\Program;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render(): View
    {
        return view('livewire.dashboard', [
            'eventCount' => Event::query()->count(),
            'publishedEventCount' => Event::query()->published()->count(),
            'programCount' => Program::query()->count(),
            'publishedProgramCount' => Program::query()->published()->count(),
            'manifestoCount' => Manifesto::query()->count(),
            'publishedManifestoCount' => Manifesto::query()->published()->count(),
            'pageCount' => Page::query()->count(),
            'publishedPageCount' => Page::query()->published()->count(),
            'recentEvents' => Event::query()->latest()->limit(6)->get(),
            'recentPrograms' => Program::query()->latest()->limit(6)->get(),
            'recentManifestos' => Manifesto::query()->orderBy('sort_order')->latest()->limit(6)->get(),
            'recentPages' => Page::query()->latest('updated_at')->limit(6)->get(),
        ]);
    }
}
