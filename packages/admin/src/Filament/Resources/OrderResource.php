<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\OrderResource\Pages;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\ManageOrder;
use Lunar\Admin\Support\Actions\Orders\UpdateStatusBulkAction;
use Lunar\Admin\Support\OrderStatus;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Admin\Support\Tables\Filters\DateRangeFilter;
use Lunar\Models\Order;

class OrderResource extends BaseResource
{
    protected static ?string $permission = 'sales:manage-orders';

    protected static ?string $model = Order::class;

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
        return static::getModel()::where('status', '=', 'in-process')->count();
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->filters(
                static::getTableFilters()
            )
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->recordUrl(fn ($record) => ManageOrder::getUrl(['record' => $record]))
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    UpdateStatusBulkAction::make('update_status')
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('id', 'DESC')
            ->paginated([10, 25, 50, 100])
            ->selectCurrentPageOnly()
            ->deferLoading();
    }

    public static function getDefaultFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('status')
                ->options(function () {
                    return collect(
                        config('lunar.orders.statuses')
                    )->mapWithKeys(
                        fn ($status, $key) => [$key => $status['label']]
                    );
                })->label(
                    __('lunarpanel::order.table.status.label')
                ),
            DateRangeFilter::make('placed_at'),
        ];
    }

    public static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('status')
                ->label(__('lunarpanel::order.table.status.label'))
                ->formatStateUsing(fn (string $state) => OrderStatus::getLabel($state))
                ->color(fn (string $state) => OrderStatus::getColor($state))
                ->badge(),
            Tables\Columns\TextColumn::make('reference')
                ->label(__('lunarpanel::order.table.reference.label'))
                ->toggleable()
                ->searchable(),
            Tables\Columns\TextColumn::make('customer_reference')
                ->label(__('lunarpanel::order.table.customer_reference.label')),
            Tables\Columns\TextColumn::make('shippingAddress.fullName')
                ->label(__('lunarpanel::order.table.customer.label')),
            Tables\Columns\TextColumn::make('shippingAddress.postcode')
                ->label(__('lunarpanel::order.table.postcode.label')),
            Tables\Columns\TextColumn::make('shippingAddress.contact_email')
                ->label(__('lunarpanel::order.table.email.label')),
            Tables\Columns\TextColumn::make('shippingAddress.contact_phone')
                ->label(__('lunarpanel::order.table.phone.label')),
            Tables\Columns\TextColumn::make('total')
                ->label(__('lunarpanel::order.table.total.label'))
                ->formatStateUsing(fn ($state): string => $state->formatted),
            Tables\Columns\TextColumn::make('placed_at')
                ->label(__('lunarpanel::order.table.date.label'))
                ->dateTime(),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            // 'create' => Pages\CreateOrder::route('/create'),
            'order' => Pages\ManageOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
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
            'shippingAddress.first_name',
            'shippingAddress.last_name',
            'shippingAddress.contact_email',
            'tags.value',
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with([
            'shippingAddress',
            'tags',
        ]);
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Order $record */
        $details = [
            __('lunarpanel::order.table.status.label') => $record->getStatusLabelAttribute(),
            __('lunarpanel::order.table.total.label') => $record->total?->formatted,
            __('lunarpanel::order.table.customer.label') => $record->shippingAddress->fullName,
        ];

        if ($record->shippingAddress->contact_email) {
            $details[__('lunarpanel::order.table.email.label')] = $record->shippingAddress->contact_email;
        }

        if ($record->placed_at) {
            $details[__('lunarpanel::order.table.date.label')] = $record->placed_at;
        }

        return $details;
    }
}
