<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\EditOrder;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\ListOrders;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\ManageOrder;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\Order\OrderTable;

class OrderResource extends BaseResource
{
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
        return static::getModel()::whereIn('status', config('lunar.panel.order_count_statuses', 'payment-received'))->count();
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

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->reference;
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return OrderResource::getUrl('order', [
            'record' => $record,
        ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'reference',
            'customer_reference',
            'notes',
            'billingAddress.first_name',
            'billingAddress.last_name',
            'billingAddress.contact_email',
            'tags.value',
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with([
            'billingAddress',
            'tags',
        ]);
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Order $record */
        $details = [
            __('lunarpanel::order.table.status.label') => $record->getStatusLabelAttribute(),
            __('lunarpanel::order.table.total.label') => $record->total?->formatted,
            __('lunarpanel::order.table.customer.label') => $record->billingAddress?->fullName,
        ];

        if ($record->billingAddress?->contact_email) {
            $details[__('lunarpanel::order.table.email.label')] = $record->billingAddress->contact_email;
        }

        if ($record->placed_at) {
            $details[__('lunarpanel::order.table.date.label')] = $record->placed_at;
        }

        return $details;
    }
}
