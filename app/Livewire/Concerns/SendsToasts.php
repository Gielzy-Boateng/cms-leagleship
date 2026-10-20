<?php

namespace App\Livewire\Concerns;

trait SendsToasts
{
    protected function jsToast(string $message): void
    {
        $payload = json_encode(['message' => $message], JSON_THROW_ON_ERROR);

        $this->js("window.dispatchEvent(new CustomEvent('toast', { detail: {$payload} }))");
    }
}
