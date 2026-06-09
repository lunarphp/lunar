<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\EditOrder;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\ListOrders;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\ManageOrder;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Filament\GlobalSearch\Concerns\HasLunarGlobalSearch;
use Lunar\Filament\GlobalSearch\OrderGlobalSearch;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\Order\OrderTable;

class OrderResource extends BaseResource
{
    use HasLunarGlobalSearch;

    protected static string $globalSearch = OrderGlobalSearch::class;

    protected static ?string $permission = 'sales:manage-orders';

    protected static ?string $model = OrderContract::class;

    protected static ?int $navigationSort = 1;

    protected static int $globalSearchResultsLimit = 5;

    public static function getLabel(): string
    {
        return __('lunarpanel::order.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::order.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::orders');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.sales');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()->whereNull('closed_at')->count();
    }

    public static function table(Table $table): Table
    {
        return Resolver::table(OrderTable::class, $table);
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'order' => ManageOrder::route('/{record}'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return OrderResource::getUrl('order', [
            'record' => $record,
        ]);
    }
}
