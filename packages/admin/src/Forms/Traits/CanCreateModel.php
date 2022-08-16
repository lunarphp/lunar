<?php

namespace GetCandy\Hub\Forms\Traits;

use Illuminate\Support\Str;

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
        $this->validate();
        $this->model->save();

        $routeName = Str::of(class_basename($this->model))->plural()->lower();
        $this->notify(
            __('adminhub::notifications.model.created', ['model' => class_basename($this->model)]),
            "hub.$routeName.index"
        );
    }
}
