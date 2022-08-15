<?php

namespace GetCandy\Hub\Forms;

use Livewire\Component;

abstract class GetCandyForm extends Component
{
    protected string $layout = 'default';

    protected string $view = 'adminhub::livewire.forms.default';

    protected function render()
    {
        return view($this->view, [
            'view' => $this->view,
            'schema' => $this->schema(),
            'showDeleteDangerZone' => $this->showDeleteDangerZone,
        ])->layout($this->layout);
    }

    abstract protected function schema(): array;
}
