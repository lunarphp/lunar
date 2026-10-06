<?php

namespace Lunar\Admin\Support\Extending;

use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

abstract class ViewPageExtension extends BaseExtension
{
    public function heading($title, Model $record): string
    {
        return $title;
    }

    public function subheading($title, Model $record): ?string
    {
        return $title;
    }

    public function extendsInfolist(Schema $schema): Schema
    {
        return $schema;
    }
}
