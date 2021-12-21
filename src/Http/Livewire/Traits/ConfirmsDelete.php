<?php

namespace GetCandy\Hub\Http\Livewire\Traits;

trait ConfirmsDelete
{
    /**
     * Defines the confirmation text when deleting a model.
     *
     * @var string|null
     */
    public $deleteConfirm = null;

    /**
     * Returns whether we have met the criteria to allow deletion.
     *
     * @return bool
     */
    abstract public function getCanDeleteProperty();
}
