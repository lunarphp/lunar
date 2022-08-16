<?php

namespace GetCandy\Hub\Forms\Traits;

trait CanDeleteModel
{
    /**
     * Defines the confirmation text when deleting a model.
     *
     * @var string|null
     */
    public ?string $deleteConfirm = null;

    /**
     * Soft deletes a brand.
     *
     * @return void
     */
    public function delete()
    {
        if (! $this->canDelete) {
            return;
        }

        $this->model->delete();

        $this->notify(
            __('adminhub::notifications.brands.deleted'),
            'hub.brands.index'
        );
    }

    /**
     * Returns whether we have met the criteria to allow deletion.
     *
     * @return bool
     */
    public function getCanDeleteProperty(): bool
    {
        return $this->deleteConfirm === $this->model->name;
    }
}
