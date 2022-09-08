<?php

namespace GetCandy\LivewireTables\Components\Concerns;

trait HasLivewireComponent
{
    /**
     * The reference to the Livewire component.
     *
     * @var string
     */
    protected $livewire = null;

    /**
     * Set the livewire component to render.
     *
     * @param  string  $livewire
     * @return self
     */
    public function livewire($livewire): self
    {
        $this->livewire = $livewire;

        return $this;
    }

    /**
     * Get the Livewire component name.
     *
     * @return string
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
     *
     * @return bool
     */
    public function isLivewire(): bool
    {
        return (bool) $this->livewire;
    }
}
