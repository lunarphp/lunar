<?php

namespace Lunar\Hub\Http\Livewire\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait HasForms
{
    /**
     * The modal forms collection.
     *
     * @var \Illuminate\Support\Collection
     */
    public Collection $modalForms;

    /**
     * The modal forms collection.
     *
     * @var \Illuminate\Support\Collection
     */
    public Collection $slideoverForms;

    /**
     * Mount the HasForms trait.
     *
     * @return void
     */
    public function mountHasForms(): void
    {
        $this->modalForms = collect();
        $this->slideoverForms = collect();
    }

    /**
     * Trigger form component.
     *
     * @param  string  $handle
     * @param  string  $uiComponent
     * @param  array  $settings
     * @return void
     */
    public function triggerForm(
        string $handle,
        string $uiComponent = 'modal',
        array $settings = []
    ): void {
        $formProperties = [
            'show' => true,
            'handle' => $handle,
            'model' => $this->getModelFromHandle($handle),
            'component' => 'hub.components.forms.'.$handle,
            'settings' => $settings,
        ];

        $formProperties['submitAction'] = $this->getSubmitAction($formProperties['model']);

        match ($uiComponent) {
            'modal' => $this->modalForms->put($handle, $formProperties),
            'slideover' => $this->slideoverForms->put($handle, $formProperties),
        };
    }

    /**
     * Get the model from the handle.
     *
     * @param  string  $handle
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function getModelFromHandle(string $handle): ?Model
    {
        $modelProperty = Str::of($handle)->before('-');

        return $this->$modelProperty ?? null;
    }

    /**
     * Get the submit action.
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @return string
     */
    protected function getSubmitAction(?Model $model): string
    {
        return $model?->exists ? 'update' : 'create';
    }

    /**
     * Dispatch the create form event.
     *
     * @return void
     */
    public function create()
    {
        $this->emit('onCreateForm');
    }

    /**
     * Dispatch the update form event.
     *
     * @return void
     */
    public function update()
    {
        $this->emit('onUpdateForm');
    }
}
