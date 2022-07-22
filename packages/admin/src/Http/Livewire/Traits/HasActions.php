<?php

namespace GetCandy\Hub\Http\Livewire\Traits;

use Illuminate\Support\Collection;

/**
 * Trait HasActions.
 *
 * @todo Trait to clean up reusable actions currently hard coded in livewire component views
 */
trait HasActions
{
    public Collection $actions;

    public function registerActions(array $actions)
    {
        $this->actions = collect($actions);
    }

    public function getActions(): Collection
    {
        return $this->actions;
    }

    protected function dispatchAction($action, $data = []): void
    {
        $this->actions->push($action);
        $action->dispatch($data);
    }
}
