<?php

namespace GetCandy\Hub\Forms\Traits;

use Illuminate\Support\Str;

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

        $routeName = Str::of(class_basename($this->model))->plural()->lower();
        $this->notify(
            __('adminhub::notifications.model.deleted', ['model' => class_basename($this->model)]),
            "hub.$routeName.index"
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
