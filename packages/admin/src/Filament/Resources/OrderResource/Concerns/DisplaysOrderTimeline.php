<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Concerns;

use Filament\Infolists;
use Lunar\Admin\Support\Infolists\Components\Timeline;

trait DisplaysOrderTimeline
{
    public static function getTimelineInfolist(): Infolists\Components\Component
    {
        return self::callLunarHook('extendTimelineInfolist', static::getDefaultTimelineInfolist());
    }

    public static function getDefaultTimelineInfolist(): Infolists\Components\Component
    {
        return Infolists\Components\Grid::make()
            ->schema([
                Timeline::make('timeline')
                    ->label(__('lunarpanel::order.infolist.timeline.label')),
            ]);
    }
}
