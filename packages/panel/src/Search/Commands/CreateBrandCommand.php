<?php

namespace Lunar\Panel\Search\Commands;

use Lunar\Panel\Search\SearchCommand;
use Lunar\Panel\Sections\Catalog\CatalogSection;
use Lunar\Panel\Support\Position;

class CreateBrandCommand extends SearchCommand
{
    public function key(): string
    {
        return 'brands.create';
    }

    public function label(): string
    {
        return __('panel::search.command_create_brand');
    }

    public function url(): string
    {
        return route('panel.brands.create');
    }

    public function permission(): string
    {
        return CatalogSection::BRANDS_PERMISSION;
    }

    public function position(): Position
    {
        return Position::priority(30);
    }
}
