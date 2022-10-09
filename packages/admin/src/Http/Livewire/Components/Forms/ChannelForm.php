<?php

namespace Lunar\Hub\Http\Livewire\Components\Forms;

use Lunar\Hub\Forms;
use Lunar\Hub\Forms\LunarForm;

class ChannelForm extends LunarForm
{
    protected bool $showDeleteDangerZone = true;

    /**
     * Returns validation rules.
     *
     * @return array
     */
    protected function rules(): array
    {
        $table = $this->model->getTable();

        return [
            'model.name' => 'required|string|max:255',
            'model.handle' => "required|string|unique:$table,handle,{$this->model->id}|max:255",
            'model.url' => 'nullable|url|max:255',
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
