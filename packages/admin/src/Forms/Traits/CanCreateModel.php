<?php

namespace GetCandy\Hub\Forms\Traits;

trait CanCreateModel
{
    /**
     * Defines the confirmation text when deleting a model.
     *
     * @var string|null
     */
    public $createConfirm = null;

    /**
     * Soft creates a brand.
     *
     * @return void
     */
    public function create()
    {
        if (! $this->canDelete) {
            return;
        }

        $this->model->create();

        $this->notify(
            __('adminhub::notifications.brands.created'),
            'hub.brands.index'
        );
    }
}
