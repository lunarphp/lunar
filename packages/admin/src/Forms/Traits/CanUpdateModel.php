<?php

namespace Lunar\Hub\Forms\Traits;

use Illuminate\Database\Eloquent\Model;
use Lunar\Hub\Http\Livewire\Traits\Notifies;

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

        $this->notify(
            message: __('adminhub::notifications.model.updated', ['model' => class_basename($this->model)]),
            route: $this->getRouteName(),
            routeParams: $this->getRouteParams(),
        );
    }
}
