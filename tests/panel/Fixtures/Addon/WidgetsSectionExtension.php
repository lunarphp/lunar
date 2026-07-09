<?php

namespace Lunar\Tests\Panel\Fixtures\Addon;

use Lunar\Panel\Sections\SectionExtension;
use Lunar\Panel\Slots\Slot;
use Lunar\Panel\Slots\SlotRegistry;

class WidgetsSectionExtension extends SectionExtension
{
    public function extends(): string
    {
        return 'widgets';
    }

    public function slots(SlotRegistry $registry): void
    {
        $registry->add(new Slot(
            zone: 'widgets.index:main:after',
            component: 'widgets-extra::Promo',
            priority: 20,
        ));
    }
}
