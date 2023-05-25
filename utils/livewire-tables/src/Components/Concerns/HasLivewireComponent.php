<?php

namespace Lunar\LivewireTables\Components\Concerns;

trait HasLivewireComponent
{
    /**
     * The reference to the Livewire component.
     *
     * @var string
     */
    public $livewire = null;

    /**
     * Set the livewire component to render.
     *
     * @param  string  $livewire
     */
    public function livewire($livewire): self
    {
        $this->livewire = $livewire;

        return $this;
    }

    /**
     * Get the Livewire component name.
     */
    public function getLivewire(): string
    {
        if (! class_exists($this->livewire)) {
            return $this->livewire;
        }

        return app($this->livewire)->getName();
    }

    /**
     * Returns whether we have the Livewire component set.
     */
    public function isLivewire(): bool
    {
        return (bool) $this->livewire;
    }
}
