<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Concerns;

use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Lunar\Admin\Support\Infolists\Components\Timeline;

trait DisplaysOrderTimeline
{
    public static function getTimelineInfolist(): Component
    {
        return self::callStaticLunarHook('extendTimelineInfolist', static::getDefaultTimelineInfolist());
    }

    public static function getDefaultTimelineInfolist(): Component
    {
        return Grid::make()
            ->schema([
                Timeline::make('timeline')
                    ->label(__('lunarpanel::order.infolist.timeline.label')),
            ]);
    }
}
