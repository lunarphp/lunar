<?php

namespace GetCandy\Hub\Http\Livewire\Components\Forms;

use GetCandy\Hub\Forms;
use GetCandy\Hub\Forms\GetCandyForm;

class ChannelForm extends GetCandyForm
{
    protected bool $showDeleteDangerZone = true;

    /**
     * Returns validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        $table = $this->model->getTable();

        return [
            'model.name'    => 'required|string|max:255',
            'model.handle'  => "required|string|unique:$table,handle,{$this->model->id}|max:255",
            'model.url'     => 'nullable|url|max:255',
            'model.default' => 'nullable',
        ];
    }

    protected function schema(): array
    {
        return [
            Forms\Fields\Input\Text::make('name')->required(),
            Forms\Fields\Input\Text::make('handle')->required(),
            Forms\Fields\Input\Text::make('url')->required(),
            Forms\Fields\Input\Toggle::make('default')
                ->label('adminhub::inputs.default.label')
                ->required(),
        ];
    }
}
