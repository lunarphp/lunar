<?php

namespace Lunar\Admin\Support\Extending;

use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;

abstract class EditPageExtension extends BaseExtension
{
    public function heading($title, Model $record): string
    {
        return $title;
    }

    public function subheading($title, Model $record): ?string
    {
        return $title;
    }

    public function formActions(array $actions): array
    {
        return $actions;
    }

    public function extendForm(Form $form): Form
    {
        return $form;
    }

    public function beforeFill(array $data): array
    {
        return $data;
    }

    public function beforeSave(array $data): array
    {
        return $data;
    }

    public function beforeUpdate(array $data, Model $record): array
    {
        return $data;
    }

    public function afterUpdate(Model $record, array $data): Model
    {
        return $record;
    }
}
