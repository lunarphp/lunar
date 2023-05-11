<?php

namespace Lunar\LivewireTables\Components\Concerns;

trait HasViewComponent
{
    /**
     * The reference to the view component.
     *
     * @var string
     */
    protected $viewComponent = null;

    /**
     * Set the view component.
     *
     * @param  string  $viewComponent
     */
    public function viewComponent($viewComponent): self
    {
        $this->viewComponent = $viewComponent;

        return $this;
    }

    /**
     * Whether the column is a view component.
     */
    public function isViewComponent(): bool
    {
        return (bool) $this->viewComponent;
    }

    /**
     * Return the reference to the view component.
     */
    public function getViewComponent(): string
    {
        return $this->viewComponent;
    }
}
