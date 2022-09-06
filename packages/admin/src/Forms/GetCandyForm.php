<?php

namespace GetCandy\Hub\Forms;

use GetCandy\Hub\Http\Livewire\Traits\WithLanguages;
use Illuminate\Support\Str;
use Livewire\Component;

abstract class GetCandyForm extends Component
{
    use Traits\HasListeners;
    use Traits\InteractsWithModel;
    use Traits\CanCreateModel;
    use Traits\CanUpdateModel;
    use Traits\CanDeleteModel;
    use Traits\HasRules;
    use WithLanguages;

    protected string $layout = 'default';

    protected string $view = 'adminhub::livewire.components.forms.base-form';

    protected bool $showDeleteDangerZone = false;

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return view($this->view, [
            'model' => $this->model,
            'schema' => $this->schema(),
            'showDeleteDangerZone' => $this->showDeleteDangerZone,
            'submitAction' => $this->getSubmitAction(),
        ])->layout($this->layout);
    }

    /**
     * Returns the submit action.
     *
     * @return string
     */
    protected function getSubmitAction(): string
    {
        return $this->model->exists ? 'update' : 'create';
    }

    /**
     * Returns the route name for redirect.
     *
     * @return string
     */
    protected function getRouteName(): string
    {
        $entity = Str::of(class_basename($this->model))->plural()->lower();

        return 'hub.'.$entity.'.'.($this->model->exists ? 'show' : 'create');
    }

    /**
     * Returns the route params for redirect.
     *
     * @return array
     */
    protected function getRouteParams(): array
    {
        $entity =  Str::of(class_basename($this->model))->lower()->__toString();

        return $this->model->exists ? [$entity => $this->model] : [];
    }

    abstract protected function rules(): array;

    abstract protected function schema(): array;
}
