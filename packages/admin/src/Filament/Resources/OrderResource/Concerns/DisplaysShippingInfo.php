<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Concerns;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\IconPosition;

trait DisplaysShippingInfo
{
    public static function getShippingInfolist(): Section
    {
        return self::callStaticLunarHook('extendShippingInfolist', static::getDefaultShippingInfolist());
    }

    public static function getDefaultShippingInfolist(): Section
    {
        return Section::make()
            ->schema([
                RepeatableEntry::make('shippingLines')
                    ->hiddenLabel()
                    ->contained(false)
                    ->columns(2)
                    ->columnSpan(12)
                    ->schema([
                        TextEntry::make('description')
                            ->icon('heroicon-s-truck')
                            ->html()
                            ->iconPosition(IconPosition::Before)
                            ->hiddenLabel(),
                        TextEntry::make('sub_total')
                            ->hiddenLabel()
                            ->alignEnd()
                            ->formatStateUsing(fn ($state) => $state->formatted),
                        TextEntry::make('notes')
                            ->hidden(
                                fn ($state) => ! $state
                            )
                            ->placeholder(
                                __('lunarpanel::order.infolist.notes.placeholder')
                            ),
                    ]),
            ]);
    }
}
