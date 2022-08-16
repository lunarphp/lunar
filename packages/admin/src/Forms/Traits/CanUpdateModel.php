<?php

namespace GetCandy\Hub\Forms\Traits;

use GetCandy\Hub\Http\Livewire\Traits\HasImages;
use GetCandy\Hub\Http\Livewire\Traits\HasUrls;
use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use Illuminate\Database\Eloquent\Model;

trait CanUpdateModel
{
    use Notifies;
    use HasImages;
    use HasUrls;

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
            __('adminhub::notifications.model.updated'),
            'hub.brands.index'
        );
    }
}
