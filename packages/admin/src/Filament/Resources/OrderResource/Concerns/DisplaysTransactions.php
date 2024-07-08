<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Concerns;

use Filament\Infolists;
use Lunar\Admin\Support\Infolists\Components\Transaction as InfolistsTransaction;

trait DisplaysTransactions
{
    public static function getDefaultTransactionsRepeatableEntry(): Infolists\Components\RepeatableEntry
    {
        return Infolists\Components\RepeatableEntry::make('transactions')
            ->hiddenLabel()
            ->placeholder(__('lunarpanel::order.infolist.transactions.placeholder'))
            ->getStateUsing(fn ($record) => $record->transactions)
            ->contained(false)
            ->schema([
                InfolistsTransaction::make('transactions'),
            ]);
    }

    public static function getTransactionsRepeatableEntry(): Infolists\Components\RepeatableEntry
    {
        return self::callStaticLunarHook('extendTransactionsRepeatableEntry', static::getDefaultTransactionsRepeatableEntry());
    }

    public static function getDefaultTransactionsInfolist(): Infolists\Components\Component
    {
        return Infolists\Components\Section::make('transactions')
            ->heading(__('lunarpanel::order.infolist.transactions.label'))
            ->compact()
            ->collapsed(fn ($state) => filled($state))
            ->collapsible(fn ($state) => filled($state))
            ->schema([
                static::getTransactionsRepeatableEntry(),
            ]);
    }

    public static function getTransactionsInfolist(): Infolists\Components\Component
    {
        return self::callStaticLunarHook('extendTransactionsInfolist', static::getDefaultTransactionsInfolist());
    }
}
