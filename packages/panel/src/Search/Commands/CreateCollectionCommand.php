<?php

namespace Lunar\Panel\Search\Commands;

use Lunar\Panel\Search\SearchCommand;
use Lunar\Panel\Sections\Catalog\CatalogSection;
use Lunar\Panel\Support\Position;

class CreateCollectionCommand extends SearchCommand
{
    public function key(): string
    {
        return 'collections.create';
    }

    public function label(): string
    {
        return __('panel::search.command_create_collection');
    }

    public function url(): string
    {
        return route('panel.collections.create');
    }

    public function permission(): string
    {
        return CatalogSection::COLLECTIONS_PERMISSION;
    }

    public function position(): Position
    {
        return Position::priority(20);
    }
}
