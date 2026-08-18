<?php

namespace Lunar\Tests\Panel\Fixtures\Customers;

use Lunar\Panel\Sections\Section;
use Lunar\Panel\Slots\Slot;
use Lunar\Panel\Slots\SlotRegistry;
use Lunar\Tests\Panel\Fixtures\Customers\Actions\ImpersonatePageAction;
use Lunar\Tests\Panel\Fixtures\Customers\Tables\CustomerFixtureTableExtension;

/**
 * A minimal add-on style section used only to prove that the Customers
 * pages genuinely consult the slot registry and table extension resolver,
 * rather than exercising those mechanisms only in isolation.
 */
class CustomerFixtureSection extends Section
{
    public function key(): string
    {
        return 'customer-fixture';
    }

    public function slots(SlotRegistry $registry): void
    {
        $registry->add(new Slot(
            zone: 'customers.edit:main:after',
            component: 'customer-fixture::Banner',
            props: ['message' => 'Injected by the fixture add-on.'],
        ));
    }

    public function tableExtensions(): array
    {
        return ['customers.index' => CustomerFixtureTableExtension::class];
    }

    public function pageActions(): array
    {
        return ['customers.edit' => [ImpersonatePageAction::class]];
    }
}
