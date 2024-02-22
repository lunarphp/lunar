<?php

namespace Lunar\Admin\Support\Extending;

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

    public function headerActions(array $actions): array
    {
        return $actions;
    }
}
