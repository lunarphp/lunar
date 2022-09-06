<?php

namespace GetCandy\Hub\Http\Livewire\Components\Forms;

use GetCandy\Hub\Forms;
use GetCandy\Hub\Forms\GetCandyForm;
use GetCandy\Models\Language;

class CustomerDetailForm extends GetCandyForm
{
    protected string $layout = 'slideover';

    /**
     * Returns validation rules.
     *
     * @return array
     */
    protected function rules(): array
    {
        $table = $this->model->getTable();

        return [
            'model.title' => 'required',
            'model.first_name' => 'required',
            'model.last_name' => 'required',
            'model.email' => 'required|email|unique:'.$table,
            'model.language' => 'required',
            'model.newsletter' => 'nullable|bool',
            'model.company' => 'nullable|string',
            'model.vat_no' => 'nullable|string',
            'model.customerGroups' => 'required',
        ];
    }

    protected function schema(): array
    {
        return [
            Forms\Fields\Input\Select::make('title')->options(['Mr.', 'Mrs.']),
            Forms\Fields\Input\Text::make('first_name')->required(),
            Forms\Fields\Input\Text::make('last_name')->required(),
            Forms\Fields\Input\Text::make('email'),
            Forms\Fields\Input\Select::make('language')->options(
                options: Language::all()->mapWithKeys(fn ($language) => [$language->id => $language->name])->toArray(),
                relationship: true,
            ),
            Forms\Fields\Input\Toggle::make('newsletter'),
            Forms\Fields\Input\Text::make('company'),
            Forms\Fields\Input\Text::make('vat_no'),
            Forms\Fields\Input\Tags::make('customerGroups')
                ->required(),
        ];
    }
}
