<?php

namespace Lunar\Panel\Search\Commands;

use Lunar\Panel\Search\SearchCommand;
use Lunar\Panel\Sections\Catalog\CatalogSection;
use Lunar\Panel\Support\Position;

class CreateProductCommand extends SearchCommand
{
    public function key(): string
    {
        return 'products.create';
    }

    public function label(): string
    {
        return __('panel::search.command_create_product');
    }

    public function url(): string
    {
        return route('panel.products.create');
    }

    public function permission(): string
    {
        return CatalogSection::PRODUCTS_PERMISSION;
    }

    public function position(): Position
    {
        return Position::priority(10);
    }
}
