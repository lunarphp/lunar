<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Concerns;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Lunar\Admin\Support\Infolists\Components\Transaction as InfolistsTransaction;

trait DisplaysTransactions
{
    public static function getDefaultTransactionsRepeatableEntry(): RepeatableEntry
    {
        return RepeatableEntry::make('transactions')
            ->hiddenLabel()
            ->placeholder(__('lunarpanel::order.infolist.transactions.placeholder'))
            ->getStateUsing(fn ($record) => $record->transactions)
            ->contained(false)
            ->schema([
                InfolistsTransaction::make('transaction_detail'),
            ]);
    }

    public static function getTransactionsRepeatableEntry(): RepeatableEntry
    {
        return self::callStaticLunarHook('extendTransactionsRepeatableEntry', static::getDefaultTransactionsRepeatableEntry());
    }

    public static function getDefaultTransactionsInfolist(): Component
    {
        return Section::make('transactions')
            ->heading(__('lunarpanel::order.infolist.transactions.label'))
            ->compact()
            ->collapsed(fn ($state) => filled($state))
            ->collapsible(fn ($state) => filled($state))
            ->schema([
                static::getTransactionsRepeatableEntry(),
            ]);
    }

    public static function getTransactionsInfolist(): Component
    {
        return self::callStaticLunarHook('extendTransactionsInfolist', static::getDefaultTransactionsInfolist());
    }
}
