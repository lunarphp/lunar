<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Concerns;

use Filament\Infolists\Components\Entry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\IconPosition;
use Lunar\Core\Models\Order;

trait DisplaysOrderSummary
{
    public static function getDefaultOrderSummaryNewCustomerEntry(): TextEntry
    {
        return TextEntry::make('new_customer')
            ->label(__('lunarpanel::order.infolist.new_returning.label'))
            ->alignEnd()
            ->formatStateUsing(fn ($state) => __('lunarpanel::order.infolist.'.($state ? 'new' : 'returning').'_customer.label'));
    }

    public static function getOrderSummaryNewCustomerEntry(): Entry
    {
        return self::callStaticLunarHook('extendOrderSummaryNewCustomerEntry', static::getDefaultOrderSummaryNewCustomerEntry());
    }

    public static function getDefaultOrderSummaryStatusEntry(): TextEntry
    {
        return TextEntry::make('closed_at')
            ->label(__('lunarpanel::order.infolist.status.label'))
            ->state(fn (Order $record): string => __('lunar::states.order.'.$record->lifecycleStatus()))
            ->alignEnd()
            ->color(fn (Order $record): string => match ($record->lifecycleStatus()) {
                'cancelled' => 'danger',
                'closed' => 'gray',
                default => 'success',
            })
            ->badge();
    }

    public static function getOrderSummaryStatusEntry(): Entry
    {
        return self::callStaticLunarHook('extendOrderSummaryStatusEntry', static::getDefaultOrderSummaryStatusEntry());
    }

    public static function getDefaultOrderSummaryPublicIdEntry(): TextEntry
    {
        return TextEntry::make('public_id')
            ->label(__('lunarpanel::components.public_id.label'))
            ->alignEnd()
            ->icon('heroicon-o-clipboard')
            ->iconPosition(IconPosition::After)
            ->copyable();
    }

    public static function getOrderSummaryPublicIdEntry(): Entry
    {
        return self::callStaticLunarHook('extendOrderSummaryPublicIdEntry', static::getDefaultOrderSummaryPublicIdEntry());
    }

    public static function getDefaultOrderReferenceEntry(): TextEntry
    {
        return TextEntry::make('reference')
            ->label(__('lunarpanel::order.infolist.reference.label'))
            ->alignEnd()
            ->icon('heroicon-o-clipboard')
            ->iconPosition(IconPosition::After)
            ->copyable();
    }

    public static function getOrderSummaryReferenceEntry(): Entry
    {
        return self::callStaticLunarHook('extendOrderSummaryReferenceEntry', static::getDefaultOrderReferenceEntry());
    }

    public static function getDefaultOrderSummaryCustomerReferenceEntry(): TextEntry
    {
        return TextEntry::make('customer_reference')
            ->label(__('lunarpanel::order.infolist.customer_reference.label'))
            ->alignEnd()
            ->icon('heroicon-o-clipboard')
            ->iconPosition(IconPosition::After)
            ->copyable();
    }

    public static function getOrderSummaryCustomerReferenceEntry(): Entry
    {
        return self::callStaticLunarHook('extendOrderSummaryCustomerReferenceEntry', static::getDefaultOrderSummaryCustomerReferenceEntry());
    }

    public static function getDefaultOrderSummaryChannelEntry(): TextEntry
    {
        return TextEntry::make('channel.name')
            ->label(__('lunarpanel::order.infolist.channel.label'))
            ->alignEnd();
    }

    public static function getOrderSummaryChannelEntry(): Entry
    {
        return self::callStaticLunarHook('extendOrderSummaryChannelEntry', static::getDefaultOrderSummaryChannelEntry());
    }

    public static function getDefaultOrderSummaryCreatedAtEntry(): TextEntry
    {
        return TextEntry::make('created_at')
            ->label(__('lunarpanel::order.infolist.date_created.label'))
            ->alignEnd()
            ->dateTime('Y-m-d h:i a')
            ->visible(fn ($record) => ! $record->placed_at);
    }

    public static function getOrderSummaryCreatedAtEntry(): Entry
    {
        return self::callStaticLunarHook('extendOrderSummaryCreatedAtEntry', static::getDefaultOrderSummaryCreatedAtEntry());
    }

    public static function getDefaultOrderSummaryPlacedAtEntry(): TextEntry
    {
        return TextEntry::make('placed_at')
            ->label(__('lunarpanel::order.infolist.date_placed.label'))
            ->alignEnd()
            ->dateTime('Y-m-d h:i a')
            ->placeholder('-');
    }

    public static function getOrderSummaryPlacedAtEntry(): Entry
    {
        return self::callStaticLunarHook('extendOrderSummaryPlacedAtEntry', static::getDefaultOrderSummaryPlacedAtEntry());
    }

    public static function getOrderSummarySchema(): array
    {
        return self::callStaticLunarHook('extendOrderSummarySchema', [
            static::getOrderSummaryNewCustomerEntry(),
            static::getOrderSummaryStatusEntry(),
            static::getOrderSummaryReferenceEntry(),
            static::getOrderSummaryPublicIdEntry(),
            static::getOrderSummaryCustomerReferenceEntry(),
            static::getOrderSummaryChannelEntry(),
            static::getOrderSummaryCreatedAtEntry(),
            static::getOrderSummaryPlacedAtEntry(),
        ]);
    }

    public static function getDefaultOrderSummaryInfolist(): Section
    {
        return Section::make()
            ->compact()
            ->inlineLabel()
            ->schema(
                static::getOrderSummarySchema()
            );
    }

    public static function getOrderSummaryInfolist(): Section
    {
        return self::callStaticLunarHook('extendOrderSummaryInfolist', static::getDefaultOrderSummaryInfolist());
    }
}
