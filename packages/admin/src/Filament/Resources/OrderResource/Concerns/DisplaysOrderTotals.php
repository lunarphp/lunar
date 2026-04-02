<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Concerns;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\HtmlString;
use Lunar\DataTypes\Price;

trait DisplaysOrderTotals
{
    public static function getDefaultDeliveryInstructionsEntry(): TextEntry
    {
        return TextEntry::make('shippingAddress.delivery_instructions')
            ->label(__('lunarpanel::order.infolist.delivery_instructions.label'))
            ->hidden(fn ($state) => blank($state));
    }

    public static function getDeliveryInstructionsEntry(): TextEntry
    {
        return self::callStaticLunarHook('extendDeliveryInstructionsEntry', static::getDefaultDeliveryInstructionsEntry());
    }

    public static function getDefaultOrderNotesEntry(): TextEntry
    {
        return TextEntry::make('notes')
            ->label(__('lunarpanel::order.infolist.notes.label'))
            ->placeholder(__('lunarpanel::order.infolist.notes.placeholder'));
    }

    public static function getOrderNotesEntry(): TextEntry
    {
        return self::callStaticLunarHook('extendOrderNotesEntry', static::getDefaultOrderNotesEntry());
    }

    public static function getOrderTotalsAsideSchema(): array
    {
        return self::callStaticLunarHook('extendOrderTotalsAsideSchema', [
            static::getDeliveryInstructionsEntry(),
            static::getOrderNotesEntry(),
        ]);
    }

    public static function getDefaultSubTotalEntry(): TextEntry
    {
        return TextEntry::make('sub_total')
            ->label(__('lunarpanel::order.infolist.sub_total.label'))
            ->inlineLabel()
            ->alignEnd()
            ->formatStateUsing(fn ($state) => $state->formatted);
    }

    public static function getSubTotalEntry(): TextEntry
    {
        return self::callStaticLunarHook('extendSubTotalEntry', static::getDefaultSubTotalEntry());
    }

    public static function getDefaultDiscountTotalEntry(): TextEntry
    {
        return TextEntry::make('discount_total')
            ->label(__('lunarpanel::order.infolist.discount_total.label'))
            ->inlineLabel()
            ->alignEnd()
            ->formatStateUsing(fn ($state) => $state->formatted);
    }

    public static function getDiscountTotalEntry(): TextEntry
    {
        return self::callStaticLunarHook('extendDiscountTotalEntry', static::getDefaultDiscountTotalEntry());
    }

    public static function getDefaultShippingBreakdownGroup(): Group
    {
        return Group::make()
            ->statePath('shipping_breakdown')
            ->schema(function ($state) {
                $shipping = [];
                foreach ($state->items ?? [] as $shippingIndex => $shippingItem) {
                    $shipping[] = TextEntry::make('shipping_'.$shippingIndex)
                        ->label(fn () => $shippingItem->name)
                        ->inlineLabel()
                        ->alignEnd()
                        ->state(fn () => $shippingItem->price->formatted);
                }

                return $shipping;
            });
    }

    public static function getShippingBreakdownGroup(): Group
    {
        return self::callStaticLunarHook('extendShippingBreakdownGroup', static::getDefaultShippingBreakdownGroup());
    }

    public static function getDefaultTaxBreakdownGroup(): Group
    {
        return Group::make()
            ->statePath('tax_breakdown')
            ->schema(function ($state) {
                $taxes = [];
                foreach ($state->amounts ?? [] as $taxIndex => $tax) {
                    $taxes[] = TextEntry::make('tax_'.$taxIndex)
                        ->label(fn () => $tax->description)
                        ->inlineLabel()
                        ->alignEnd()
                        ->state(fn () => $tax->price->formatted);
                }

                return $taxes;
            });
    }

    public static function getTaxBreakdownGroup(): Group
    {
        return self::callStaticLunarHook('extendTaxBreakdownGroup', static::getDefaultTaxBreakdownGroup());
    }

    public static function getDefaultTotalEntry(): TextEntry
    {
        return TextEntry::make('total')
            ->label(fn () => new HtmlString('<b>'.__('lunarpanel::order.infolist.total.label').'</b>'))
            ->inlineLabel()
            ->alignEnd()
            ->weight(FontWeight::Bold)
            ->formatStateUsing(fn ($state) => $state->formatted);
    }

    public static function getTotalEntry(): TextEntry
    {
        return self::callStaticLunarHook('extendTotalEntry', static::getDefaultTotalEntry());
    }

    public static function getDefaultPaidEntry(): TextEntry
    {
        return TextEntry::make('paid')
            ->label(fn () => __('lunarpanel::order.infolist.paid.label'))
            ->inlineLabel()
            ->alignEnd()
            ->weight(FontWeight::SemiBold)
            ->getStateUsing(function ($record) {
                $paid = $record->transactions()
                    ->whereType('capture')
                    ->whereSuccess(true)
                    ->get()
                    ->sum('amount.value');

                return (new Price($paid, $record->currency))->formatted;
            });
    }

    public static function getPaidEntry(): TextEntry
    {
        return self::callStaticLunarHook('extendPaidEntry', static::getDefaultPaidEntry());
    }

    public static function getDefaultRefundEntry(): TextEntry
    {
        return TextEntry::make('refund')
            ->label(fn () => __('lunarpanel::order.infolist.refund.label'))
            ->inlineLabel()
            ->alignEnd()
            ->color('warning')
            ->weight(FontWeight::SemiBold)
            ->getStateUsing(function ($record) {
                $paid = $record->transactions()
                    ->whereType('refund')
                    ->get()
                    ->sum('amount.value');

                return (new Price($paid, $record->currency))->formatted;
            });
    }

    public static function getRefundEntry(): TextEntry
    {
        return self::callStaticLunarHook('extendRefundEntry', static::getDefaultRefundEntry());
    }

    public static function getOrderTotalsSchema(): array
    {
        return self::callStaticLunarHook('extendOrderTotalsSchema', [
            static::getSubTotalEntry(),
            static::getDiscountTotalEntry(),
            static::getShippingBreakdownGroup(),
            static::getTaxBreakdownGroup(),
            static::getTotalEntry(),
            static::getPaidEntry(),
            static::getRefundEntry(),
        ]);
    }

    public static function getDefaultOrderTotalsInfolist(): Component
    {
        return Section::make()
            ->schema([
                Grid::make()
                    ->columns(2)
                    ->schema([
                        Grid::make()
                            ->columns(1)
                            ->columnSpan(1)
                            ->schema(
                                static::getOrderTotalsAsideSchema()
                            ),
                        Grid::make()
                            ->columns(1)
                            ->columnSpan(1)
                            ->schema(
                                static::getOrderTotalsSchema()
                            ),
                    ]),
            ]);
    }

    public static function getOrderTotalsInfolist(): Section
    {
        return self::callStaticLunarHook('extendOrderTotalsInfolist', static::getDefaultOrderTotalsInfolist());
    }
}
