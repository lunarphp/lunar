<?php

namespace Lunar\Admin\Support\Extending;

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

    public function getTabs(array $tabs): array
    {
        return $tabs;
    }

    public function relationManagers(array $managers): array
    {
        return $managers;
    }
}
