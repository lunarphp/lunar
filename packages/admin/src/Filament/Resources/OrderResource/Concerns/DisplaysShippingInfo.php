<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Concerns;

use Filament\Infolists;
use Filament\Support\Enums\IconPosition;

trait DisplaysShippingInfo
{
    public static function getShippingInfolist(): Infolists\Components\Section
    {
        return self::callStaticLunarHook('extendShippingInfolist', static::getDefaultShippingInfolist());
    }

    public static function getDefaultShippingInfolist(): Infolists\Components\Section
    {
        return Infolists\Components\Section::make()
            ->schema([
                Infolists\Components\RepeatableEntry::make('shippingLines')
                    ->hiddenLabel()
                    ->contained(false)
                    ->columns(2)
                    ->columnSpan(12)
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->icon('heroicon-s-truck')
                            ->html()
                            ->iconPosition(IconPosition::Before)
                            ->hiddenLabel(),
                        Infolists\Components\TextEntry::make('sub_total')
                            ->hiddenLabel()
                            ->alignEnd()
                            ->formatStateUsing(fn ($state) => $state->formatted),
                        Infolists\Components\TextEntry::make('notes')
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
