<?php

namespace GetCandy\Hub\Forms\Traits;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait CanUpdateModel
{
    use Notifies;

    /**
     * Defines the confirmation text when deleting a model.
     *
     * @var string|null
     */
    public $updateConfirm = null;

    public function getMediaModel(): Model
    {
        return $this->model;
    }

    public function getHasUrlsModel(): Model
    {
        return $this->model;
    }

    /**
     * Soft updates a brand.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();
        $this->model->save();

        if ($this->model->isRelation('media')) {
            $this->updateImages();
        }

        if ($this->model->isRelation('urls')) {
            $this->saveUrls();
        }

        $routeName = Str::of(class_basename($this->model))->plural()->lower();
        $this->notify(
            __('adminhub::notifications.model.updated', ['model' => class_basename($this->model)]),
            "hub.$routeName.index"
        );
    }
}
