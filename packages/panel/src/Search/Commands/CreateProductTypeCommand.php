<?php

namespace Lunar\Panel\Search\Commands;

use Lunar\Panel\Search\SearchCommand;
use Lunar\Panel\Sections\Catalog\CatalogSection;
use Lunar\Panel\Support\Position;

class CreateProductTypeCommand extends SearchCommand
{
    public function key(): string
    {
        return 'product-types.create';
    }

    public function label(): string
    {
        return __('panel::search.command_create_product_type');
    }

    public function url(): string
    {
        return route('panel.product-types.create');
    }

    public function permission(): string
    {
        return CatalogSection::PRODUCT_TYPES_PERMISSION;
    }

    public function position(): Position
    {
        return Position::priority(60);
    }
}
