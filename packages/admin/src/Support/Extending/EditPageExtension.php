<?php

namespace Lunar\Admin\Support\Extending;

use Filament\Schemas\Schema;
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

    public function extendForm(Schema $schema): Schema
    {
        return $schema;
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
