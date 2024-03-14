<?php

namespace Lunar\Admin\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\OrderResource\Pages;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\ManageOrder;
use Lunar\Admin\Support\Actions\Orders\UpdateStatusBulkAction;
use Lunar\Admin\Support\CustomerStatus;
use Lunar\Admin\Support\OrderStatus;
use Lunar\Admin\Support\Resources\BaseResource;
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
            ->filters(static::getTableFilters())
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn ($record) => ManageOrder::getUrl(['record' => $record])),
            ])
            ->recordUrl(fn ($record) => ManageOrder::getUrl(['record' => $record]))
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    UpdateStatusBulkAction::make('update_status')
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('id', 'DESC')
            ->selectCurrentPageOnly()
            ->deferLoading()
            ->poll('60s');
    }

    public static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('status')
                ->label(__('lunarpanel::order.table.status.label'))
                ->toggleable()
                ->formatStateUsing(fn (string $state) => OrderStatus::getLabel($state))
                ->color(fn (string $state) => OrderStatus::getColor($state))
                ->badge(),
            Tables\Columns\TextColumn::make('reference')
                ->label(__('lunarpanel::order.table.reference.label'))
                ->toggleable()
                ->searchable(),
            Tables\Columns\TextColumn::make('customer_reference')
                ->label(__('lunarpanel::order.table.customer_reference.label'))
                ->toggleable(),
            Tables\Columns\TextColumn::make('shippingAddress.fullName')
                ->label(__('lunarpanel::order.table.customer.label'))
                ->toggleable(),
            Tables\Columns\TextColumn::make('new_customer')
                ->label(__('lunarpanel::order.table.new_customer.label'))
                ->toggleable()
                ->formatStateUsing(fn (bool $state) => CustomerStatus::getLabel($state))
                ->color(fn (bool $state) => CustomerStatus::getColor($state))
                ->icon(fn (bool $state) => CustomerStatus::getIcon($state))
                ->badge(),
            Tables\Columns\TextColumn::make('tags.value')
                ->label(__('lunarpanel::order.table.tags.label'))
                ->badge()
                ->toggleable()
                ->separator(','),
            Tables\Columns\TextColumn::make('shippingAddress.postcode')
                ->label(__('lunarpanel::order.table.postcode.label'))
                ->toggleable(),
            Tables\Columns\TextColumn::make('shippingAddress.contact_email')
                ->label(__('lunarpanel::order.table.email.label'))
                ->toggleable()
                ->copyable()
                ->copyMessage(__('lunarpanel::order.table.email.copy_message'))
                ->copyMessageDuration(1500),
            Tables\Columns\TextColumn::make('shippingAddress.contact_phone')
                ->label(__('lunarpanel::order.table.phone.label'))
                ->toggleable(),
            Tables\Columns\TextColumn::make('total')
                ->label(__('lunarpanel::order.table.total.label'))
                ->toggleable()
                ->formatStateUsing(fn ($state): string => $state->formatted),
            Tables\Columns\TextColumn::make('placed_at')
                ->label(__('lunarpanel::order.table.date.label'))
                ->toggleable()
                ->dateTime(),
        ];
    }

    public static function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('status')
                ->label(__('lunarpanel::order.table.status.label'))
                ->options(collect(config('lunar.orders.statuses', []))
                    ->mapWithKeys(fn ($data, $status) => [$status => $data['label']])),
            Tables\Filters\Filter::make('placed_at')

                ->form([
                    Forms\Components\DatePicker::make('placed_after')
                        ->label(__('lunarpanel::order.table.placed_after.label'))
                        ->default(Carbon::now()->subMonths(6)),
                    Forms\Components\DatePicker::make('placed_before')
                        ->label(__('lunarpanel::order.table.placed_before.label')),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['placed_after'],
                            fn (Builder $query, $date): Builder => $query->whereDate('placed_at', '>=', $date),
                        )
                        ->when(
                            $data['placed_before'],
                            fn (Builder $query, $date): Builder => $query->whereDate('placed_at', '<=', $date),
                        );
                })
                ->indicateUsing(function (array $data): array {
                    $indicators = [];

                    if ($data['placed_after'] ?? null) {
                        $indicators[] = Indicator::make(__('lunarpanel::order.table.placed_after.label').' '.Carbon::parse($data['placed_after'])->toFormattedDateString())
                            ->removeField('placed_after');
                    }

                    if ($data['placed_before'] ?? null) {
                        $indicators[] = Indicator::make(__('lunarpanel::order.table.placed_before.label').' '.Carbon::parse($data['placed_before'])->toFormattedDateString())
                            ->removeField('placed_before');
                    }

                    return $indicators;
                }),
            Tables\Filters\SelectFilter::make('tags')
                ->label(__('lunarpanel::order.table.tags.label'))
                ->multiple()
                ->relationship('tags', 'value'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getDefaultPages(): array
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
