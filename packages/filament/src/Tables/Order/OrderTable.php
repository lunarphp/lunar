<?php

namespace Lunar\Filament\Tables\Order;

use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Filament\Actions\Orders\UpdateOrderStatusBulkAction;
use Lunar\Filament\Support\Concerns\CallsHooks;
use Lunar\Filament\Support\CustomerStatus;
use Lunar\Filament\Support\OrderStatus;
use Lunar\Filament\Support\RecordUrls;

class OrderTable
{
    use CallsHooks;

    public static function configure(Table $table): Table
    {
        return self::callStaticLunarHook(
            'configureTable',
            $table
                ->columns(static::getColumns())
                ->filters(static::getFilters())
                ->modifyQueryUsing(
                    fn (Builder $query): Builder => $query->with(['currency', 'billingAddress', 'tags'])
                )
                ->persistFiltersInSession()
                ->recordActions([
                    EditAction::make()
                        ->url(fn ($record) => RecordUrls::for('order', $record)),
                ])
                ->recordUrl(fn ($record) => RecordUrls::for('order', $record))
                ->toolbarActions([
                    BulkActionGroup::make([
                        UpdateOrderStatusBulkAction::make()
                            ->deselectRecordsAfterCompletion(),
                    ]),
                ])
                ->defaultSort('id', 'DESC')
                ->selectCurrentPageOnly()
                ->deferLoading()
                ->poll('60s'),
        );
    }

    public static function getColumns(): array
    {
        return [
            TextColumn::make('status')
                ->label(__('lunar-filament::order.table.status.label'))
                ->toggleable()
                ->formatStateUsing(fn (string $state) => OrderStatus::getLabel($state))
                ->color(fn (string $state) => OrderStatus::getColor($state))
                ->badge(),
            TextColumn::make('reference')
                ->label(__('lunar-filament::order.table.reference.label'))
                ->toggleable()
                ->searchable(),
            TextColumn::make('customer_reference')
                ->label(__('lunar-filament::order.table.customer_reference.label'))
                ->toggleable()
                ->searchable(),
            TextColumn::make('billingAddress.fullName')
                ->label(__('lunar-filament::order.table.customer.label'))
                ->toggleable()
                ->searchable(['first_name', 'last_name']),
            TextColumn::make('new_customer')
                ->label(__('lunar-filament::order.table.new_customer.label'))
                ->toggleable()
                ->formatStateUsing(fn (bool $state) => CustomerStatus::getLabel($state))
                ->color(fn (bool $state) => CustomerStatus::getColor($state))
                ->icon(fn (bool $state) => CustomerStatus::getIcon($state))
                ->badge(),
            TextColumn::make('tags.value')
                ->label(__('lunar-filament::order.table.tags.label'))
                ->badge()
                ->toggleable()
                ->separator(','),
            TextColumn::make('billingAddress.postcode')
                ->label(__('lunar-filament::order.table.postcode.label'))
                ->toggleable()
                ->searchable(),
            TextColumn::make('billingAddress.contact_email')
                ->label(__('lunar-filament::order.table.email.label'))
                ->toggleable()
                ->copyable()
                ->copyMessage(__('lunar-filament::order.table.email.copy_message'))
                ->copyMessageDuration(1500)
                ->searchable(),
            TextColumn::make('billingAddress.contact_phone')
                ->label(__('lunar-filament::order.table.phone.label'))
                ->toggleable(),
            TextColumn::make('total')
                ->label(__('lunar-filament::order.table.total.label'))
                ->toggleable()
                ->formatStateUsing(fn ($state): string => $state->formatted),
            TextColumn::make('placed_at')
                ->label(__('lunar-filament::order.table.date.label'))
                ->toggleable()
                ->dateTime(),
        ];
    }

    public static function getFilters(): array
    {
        return [
            SelectFilter::make('status')
                ->label(__('lunar-filament::order.table.status.label'))
                ->options(collect(config('lunar.orders.statuses', []))
                    ->mapWithKeys(fn ($data, $status) => [$status => $data['label']]))
                ->multiple(),
            Filter::make('placed_at')
                ->schema([
                    DatePicker::make('placed_after')
                        ->label(__('lunar-filament::order.table.placed_after.label'))
                        ->default(Carbon::now()->subMonths(6)),
                    DatePicker::make('placed_before')
                        ->label(__('lunar-filament::order.table.placed_before.label')),
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
                        $indicators[] = Indicator::make(__('lunar-filament::order.table.placed_after.label').' '.Carbon::parse($data['placed_after'])->toFormattedDateString())
                            ->removeField('placed_after');
                    }

                    if ($data['placed_before'] ?? null) {
                        $indicators[] = Indicator::make(__('lunar-filament::order.table.placed_before.label').' '.Carbon::parse($data['placed_before'])->toFormattedDateString())
                            ->removeField('placed_before');
                    }

                    return $indicators;
                }),
            SelectFilter::make('tags')
                ->label(__('lunar-filament::order.table.tags.label'))
                ->multiple()
                ->relationship('tags', 'value'),
        ];
    }
}
