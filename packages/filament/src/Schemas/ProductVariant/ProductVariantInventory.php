<?php

namespace Lunar\Filament\Schemas\ProductVariant;

use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Lunar\Core\Models\Location;
use Lunar\Filament\Actions\Products\AdjustStockAction;
use Lunar\Filament\Support\Concerns\CallsHooks;

/**
 * The built-in, swappable per-variant inventory controls: a read-only stock
 * rollup summary, the selling-policy fields, and recent movements. Stock is
 * adjusted through the AdjustStock header action, never edited inline — the
 * levels are ledger-derived.
 *
 * Deliberately minimal: per-location editing, the full ledger history and
 * reservation management are left to add-ons. Change it via the
 * `configureInventory` hook or by subclass-and-rebind; disable it entirely with
 * `LunarPanel::withoutInventoryControls()`.
 */
class ProductVariantInventory
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        return self::callStaticLunarHook(
            'configureInventory',
            $schema->components([
                static::getRollupSection(),
                static::getSellingPolicySection(),
                static::getRecentMovementsSection(),
            ]),
        );
    }

    /**
     * The default location these single-location controls manage — the one the
     * AdjustStock action records against. Null when no location is set up.
     */
    public static function locationDescription(): ?string
    {
        $location = Location::getDefault();

        return $location
            ? __('lunar-filament::productvariant.inventory.location', ['location' => $location->name])
            : null;
    }

    public static function getRollupSection(): Section
    {
        return Section::make(__('lunar-filament::productvariant.inventory.summary_heading'))
            ->description(static::locationDescription())
            ->headerActions([
                AdjustStockAction::make(),
            ])
            ->schema([
                static::getRollupPlaceholder('stock_on_hand', 'on_hand'),
                static::getRollupPlaceholder('stock_available', 'available'),
                static::getRollupPlaceholder('stock_committed', 'committed'),
                static::getRollupPlaceholder('stock_reserved', 'reserved'),
            ])
            ->columns(['default' => 2, 'xl' => 4]);
    }

    public static function getRollupPlaceholder(string $attribute, string $key): Placeholder
    {
        return Placeholder::make($attribute)
            ->label(__("lunar-filament::productvariant.inventory.{$key}"))
            ->content(fn ($record): string => (string) ($record?->{$attribute} ?? 0));
    }

    public static function getSellingPolicySection(): Section
    {
        return Section::make()
            ->schema([
                ProductVariantForm::getSellingPolicyComponent()->live(),
                ProductVariantForm::getBackorderComponent()
                    ->disabled(fn (Get $get): bool => $get('selling_policy') !== 'in_stock_or_on_backorder'),
                ProductVariantForm::getUnitQtyComponent(),
                ProductVariantForm::getQuantityIncrementComponent(),
                ProductVariantForm::getMinQuantityComponent(),
            ])
            ->columns(['default' => 1, 'sm' => 2, 'xl' => 3]);
    }

    public static function getRecentMovementsSection(): Section
    {
        return Section::make(__('lunar-filament::productvariant.inventory.recent_movements'))
            ->schema([
                Placeholder::make('recent_movements')
                    ->hiddenLabel()
                    ->content(fn ($record) => static::recentMovements($record)),
            ]);
    }

    /**
     * A read-only summary of the variant's most recent stock movements.
     */
    public static function recentMovements(mixed $record): HtmlString|string
    {
        $movements = $record?->stockMovements()
            ->with('location')
            ->latest()
            ->limit(5)
            ->get();

        if (blank($movements) || $movements->isEmpty()) {
            return __('lunar-filament::productvariant.inventory.no_movements');
        }

        return new HtmlString(
            $movements->map(fn ($movement): string => e(sprintf(
                '%+d %s%s - %s',
                $movement->quantity,
                $movement->type->value,
                $movement->location ? ' ('.$movement->location->name.')' : '',
                $movement->created_at?->diffForHumans() ?? '',
            )))->implode('<br>')
        );
    }
}
