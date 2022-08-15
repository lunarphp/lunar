<?php

namespace GetCandy\Hub\Http\Livewire\Components\Forms;

use GetCandy\Hub\Forms;
use GetCandy\Hub\Forms\GetCandyForm;

class ShoppingCartForm extends GetCandyForm
{
    protected string $view = 'adminhub::livewire.forms.shopping-cart';

    protected bool $showDeleteDangerZone = true;

    protected array $translatable = [
        'name',
    ];

    protected array $loadWithRelations = [
        //'thumbnail'
    ];

    protected function schema(): array
    {
        return [
            Forms\Fields\Input\Text::make('name')->required(),
            //Forms\ImageManager::make('thumb'),
            //Forms\UrlManager::make('slug')->required(),
        ];
    }
}
