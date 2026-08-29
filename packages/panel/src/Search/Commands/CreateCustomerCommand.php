<?php

namespace Lunar\Panel\Search\Commands;

use Lunar\Panel\Search\SearchCommand;
use Lunar\Panel\Sections\Sales\SalesSection;
use Lunar\Panel\Support\Position;

class CreateCustomerCommand extends SearchCommand
{
    public function key(): string
    {
        return 'customers.create';
    }

    public function label(): string
    {
        return __('panel::search.command_create_customer');
    }

    public function url(): string
    {
        return route('panel.customers.create');
    }

    public function permission(): string
    {
        return SalesSection::CUSTOMERS_PERMISSION;
    }

    public function position(): Position
    {
        return Position::priority(40);
    }
}
