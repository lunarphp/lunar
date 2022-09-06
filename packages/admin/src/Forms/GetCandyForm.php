<?php

namespace GetCandy\Hub\Forms;

use GetCandy\Hub\Http\Livewire\Traits\WithLanguages;
use Livewire\Component;

abstract class GetCandyForm extends Component
{
    use Traits\InteractsWithModel;
    use Traits\CanCreateModel;
    use Traits\CanUpdateModel;
    use Traits\CanDeleteModel;
    use Traits\HasRules;
    use WithLanguages;

    protected string $layout = 'default';

    protected string $view = 'adminhub::livewire.components.forms.base-form';

    protected bool $showDeleteDangerZone = false;

    public function render()
    {
        return view($this->view, [
            'model' => $this->model,
            'schema' => $this->schema(),
            'showDeleteDangerZone' => $this->showDeleteDangerZone,
            'submitAction' => $this->getSubmitAction(),
        ])->layout($this->layout);
    }

    protected function getSubmitAction(): string
    {
        return $this->model->exists ? 'update' : 'create';
    }

    abstract protected function rules(): array;

    abstract protected function schema(): array;
}
