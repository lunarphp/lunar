<?php

namespace Lunar\Admin\Support\Extending;

use Filament\Tables\Table;

abstract class ListPageExtension extends BaseExtension
{
    public function heading($title): string
    {
        return $title;
    }

    public function subheading($title): ?string
    {
        return $title;
    }

    public function relationManagers(array $managers): array
    {
        return $managers;
    }
}
