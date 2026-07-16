<?php

namespace Lunar\Tests\Panel\Fixtures\Channels;

use Lunar\Panel\Sections\Section;
use Lunar\Tests\Panel\Fixtures\Channels\Tables\ChannelFixtureTableExtension;

/**
 * An add-on style section proving the Channels settings page consults the
 * table extension resolver the same way Customers does — columns with query
 * hooks populate values and bulk actions ship as props.
 */
class ChannelFixtureSection extends Section
{
    public function key(): string
    {
        return 'channel-fixture';
    }

    public function tableExtensions(): array
    {
        return ['channels.index' => ChannelFixtureTableExtension::class];
    }
}
