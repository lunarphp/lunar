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

    public function render()
    {
        return view($this->view, [
            'schema' => $this->schema(),
            'showDeleteDangerZone' => $this->showDeleteDangerZone,
        ])->layout($this->layout);
    }

    abstract protected function schema(): array;
}
