<?php

namespace Lunar\Admin\Support\Extending;

use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

abstract class CreatePageExtension extends BaseExtension
{
    public function heading($title): string
    {
        return $title;
    }

    public function subheading($title): ?string
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
