<?php

namespace Lunar\Admin\Support\Extending;

use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;

abstract class CreatePageExtension extends BaseExtension
{
    public function formActions(array $actions): array
    {
        return $actions;
    }

    public function extendForm(Form $form): Form
    {
        return $form;
    }

    public function beforeCreate(array $data): array
    {
        return $data;
    }

    public function beforeCreation(array $data): array
    {
        return $data;
    }

    public function afterCreation(Model $record, array $data): Model
    {
        return $record;
    }
}
