<?php

namespace GetCandy\Hub\Http\Livewire\Components\Forms;

use GetCandy\Hub\Forms;
use GetCandy\Hub\Forms\GetCandyForm;

class CustomerDetailForm extends GetCandyForm
{
    public string $layout = 'slideover';

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return [
            'model.title' => 'required',
            'model.first_name' => 'required',
            'model.last_name' => 'required',
            'model.email' => 'required|email',
            //'model.language' => 'required',
            'model.newsletter' => 'nullable|bool',
            'model.company_name' => 'nullable|string',
            'model.vat_no' => 'nullable|string',
            'model.customerGroups' => 'nullable',
        ];
    }

    /**
     * Setup form schema.
     *
     * @return array
     */
    protected function schema(): array
    {
        return [
            Forms\Fields\Input\Select::make('title')->options(['Mr.', 'Mrs.']),
            Forms\Fields\Input\Text::make('first_name')->required(),
            Forms\Fields\Input\Text::make('last_name')->required(),
            Forms\Fields\Input\Text::make('email'),
            // Forms\Fields\Input\Select::make('language')->options(
            //     options: Language::all()->mapWithKeys(fn ($language) => [$language->id => $language->name])->toArray(),
            //     relationship: true,
            // ),
            Forms\Fields\Input\Toggle::make('newsletter'),
            Forms\Fields\Input\Text::make('company_name'),
            Forms\Fields\Input\Text::make('vat_no'),
            Forms\Fields\Input\Tags::make('customerGroups'),
        ];
    }
}
