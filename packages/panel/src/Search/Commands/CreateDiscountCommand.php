<?php

namespace Lunar\Panel\Search\Commands;

use Lunar\Panel\Search\SearchCommand;
use Lunar\Panel\Sections\Sales\SalesSection;
use Lunar\Panel\Support\Position;

class CreateDiscountCommand extends SearchCommand
{
    public function key(): string
    {
        return 'discounts.create';
    }

    public function label(): string
    {
        return __('panel::search.command_create_discount');
    }

    public function url(): string
    {
        return route('panel.discounts.create');
    }

    public function permission(): string
    {
        return SalesSection::DISCOUNTS_PERMISSION;
    }

    public function position(): Position
    {
        return Position::priority(50);
    }
}
