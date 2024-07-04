<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Concerns;

use Filament\Infolists;
use Filament\Support\Enums\IconPosition;
use Lunar\Admin\Support\OrderStatus;

trait DisplaysOrderSummary
{
    public static function getDefaultOrderSummaryNewCustomerEntry(): Infolists\Components\TextEntry
    {
        return Infolists\Components\TextEntry::make('new_customer')
            ->label(__('lunarpanel::order.infolist.new_returning.label'))
            ->alignEnd()
            ->formatStateUsing(fn ($state) => __('lunarpanel::order.infolist.'.($state ? 'new' : 'returning').'_customer.label'));
    }

    public static function getOrderSummaryNewCustomerEntry(): Infolists\Components\Entry
    {
        return self::callLunarHook('extendOrderSummaryNewCustomerEntry', static::getDefaultOrderSummaryNewCustomerEntry());
    }

    public static function getDefaultOrderSummaryStatusEntry(): Infolists\Components\TextEntry
    {
        return Infolists\Components\TextEntry::make('status')
            ->label(__('lunarpanel::order.infolist.status.label'))
            ->formatStateUsing(fn ($state) => OrderStatus::getLabel($state))
            ->alignEnd()
            ->color(fn ($state) => OrderStatus::getColor($state))
            ->badge();
    }

    public static function getOrderSummaryStatusEntry(): Infolists\Components\Entry
    {
        return self::callLunarHook('extendOrderSummaryStatusEntry', static::getDefaultOrderSummaryStatusEntry());
    }

    public static function getDefaultOrderReferenceEntry(): Infolists\Components\TextEntry
    {
        return Infolists\Components\TextEntry::make('reference')
            ->label(__('lunarpanel::order.infolist.reference.label'))
            ->alignEnd()
            ->icon('heroicon-o-clipboard')
            ->iconPosition(IconPosition::After)
            ->copyable();
    }

    public static function getOrderSummaryReferenceEntry(): Infolists\Components\Entry
    {
        return self::callLunarHook('extendOrderSummaryReferenceEntry', static::getDefaultOrderReferenceEntry());
    }

    public static function getDefaultOrderSummaryCustomerReferenceEntry(): Infolists\Components\TextEntry
    {
        return Infolists\Components\TextEntry::make('customer_reference')
            ->label(__('lunarpanel::order.infolist.customer_reference.label'))
            ->alignEnd()
            ->icon('heroicon-o-clipboard')
            ->iconPosition(IconPosition::After)
            ->copyable();
    }

    public static function getOrderSummaryCustomerReferenceEntry(): Infolists\Components\Entry
    {
        return self::callLunarHook('extendOrderSummaryCustomerReferenceEntry', static::getDefaultOrderSummaryCustomerReferenceEntry());
    }

    public static function getDefaultOrderSummaryChannelEntry(): Infolists\Components\TextEntry
    {
        return Infolists\Components\TextEntry::make('channel.name')
            ->label(__('lunarpanel::order.infolist.channel.label'))
            ->alignEnd();
    }

    public static function getOrderSummaryChannelEntry(): Infolists\Components\Entry
    {
        return self::callLunarHook('extendOrderSummaryChannelEntry', static::getDefaultOrderSummaryChannelEntry());
    }

    public static function getDefaultOrderSummaryCreatedAtEntry(): Infolists\Components\TextEntry
    {
        return Infolists\Components\TextEntry::make('created_at')
            ->label(__('lunarpanel::order.infolist.date_created.label'))
            ->alignEnd()
            ->dateTime('Y-m-d h:i a')
            ->visible(fn ($record) => ! $record->placed_at);
    }

    public static function getOrderSummaryCreatedAtEntry(): Infolists\Components\Entry
    {
            return self::callLunarHook('extendOrderSummaryCreatedAtEntry', static::getDefaultOrderSummaryCreatedAtEntry());
    }

    public static function getDefaultOrderSummaryPlacedAtEntry(): Infolists\Components\TextEntry
    {
        return Infolists\Components\TextEntry::make('placed_at')
            ->label(__('lunarpanel::order.infolist.date_placed.label'))
            ->alignEnd()
            ->dateTime('Y-m-d h:i a')
            ->placeholder('-');
    }

    public static function getOrderSummaryPlacedAtEntry(): Infolists\Components\Entry
    {
        return self::callLunarHook('extendOrderSummaryPlacedAtEntry', static::getDefaultOrderSummaryPlacedAtEntry());
    }

    public static function getOrderSummarySchema(): array
    {
        return self::callLunarHook('extendOrderSummarySchema', [
            static::getOrderSummaryNewCustomerEntry(),
            static::getOrderSummaryStatusEntry(),
            static::getOrderSummaryReferenceEntry(),
            static::getOrderSummaryCustomerReferenceEntry(),
            static::getOrderSummaryChannelEntry(),
            static::getOrderSummaryCreatedAtEntry(),
            static::getOrderSummaryPlacedAtEntry(),
        ]);
    }

    public static function getDefaultOrderSummaryInfolist(): Infolists\Components\Section
    {
        return Infolists\Components\Section::make()
            ->compact()
            ->inlineLabel()
            ->schema(
                static::getOrderSummarySchema()
            );
    }

    public static function getOrderSummaryInfolist(): Infolists\Components\Section
    {
        return self::callLunarHook('exendOrderSummaryInfolist', static::getDefaultOrderSummaryInfolist());
    }
}
