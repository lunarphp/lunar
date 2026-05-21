<?php

namespace Lunar\Admin\Support\Infolists\Components;

use Closure;
use Filament\Infolists\Components\Entry;

class Livewire extends Entry
{
    protected string $view = 'lunarpanel::infolists.components.livewire';

    protected string $livewireComponent;

    protected ?Closure $configureComponentUsing = null;

    public function content(string $livewireComponent): static
    {
        $this->livewireComponent = $livewireComponent;

        return $this;
    }

    public function getContent()
    {
        return $this->getContentName();
    }

    public function getContentName(): string
    {
        return app('livewire.factory')->resolveComponentName($this->livewireComponent);
    }
}
