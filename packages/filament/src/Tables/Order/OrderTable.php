<?php

namespace Lunar\Filament\Tables\Order;

use Carbon\Carbon;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Fulfilment\FulfilmentStatus;
use Lunar\Core\States\Order\Payment\PaymentStatus;
use Lunar\Filament\Support\Concerns\CallsHooks;
use Lunar\Filament\Support\CustomerStatus;
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
                ->defaultSort('id', 'DESC')
                ->selectCurrentPageOnly()
                ->deferLoading()
                ->poll('60s'),
        );
    }

    public static function getColumns(): array
    {
        return [
            TextColumn::make('closed_at')
                ->label(__('lunar-filament::order.table.status.label'))
                ->toggleable()
                ->state(fn (Order $record): string => __('lunar::states.order.'.$record->lifecycleStatus()))
                ->color(fn (Order $record): string => match ($record->lifecycleStatus()) {
                    'cancelled' => 'danger',
                    'closed' => 'gray',
                    default => 'success',
                })
                ->badge(),
            TextColumn::make('payment_status')
                ->label(__('lunar-filament::order.table.payment_status.label'))
                ->toggleable()
                ->formatStateUsing(fn ($state) => $state instanceof PaymentStatus ? $state->label() : (string) $state)
                ->badge(),
            TextColumn::make('fulfilment_status')
                ->label(__('lunar-filament::order.table.fulfilment_status.label'))
                ->toggleable()
                ->formatStateUsing(fn ($state) => $state instanceof FulfilmentStatus ? $state->label() : (string) $state)
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
                ->formatStateUsing(fn ($state, $record): string => $record->format('total')),
            TextColumn::make('placed_at')
                ->label(__('lunar-filament::order.table.date.label'))
                ->toggleable()
                ->dateTime(),
            TextColumn::make('public_id')
                ->label(__('lunar-filament::components.public_id.label'))
                ->toggleable(isToggledHiddenByDefault: true)
                ->copyable(),
        ];
    }

    public static function getFilters(): array
    {
        return [
            TernaryFilter::make('closed_at')
                ->label(__('lunar-filament::order.table.status.label'))
                ->trueLabel(__('lunar::states.order.closed'))
                ->falseLabel(__('lunar::states.order.open'))
                // Default to the "open" work queue — the inbox-zero landing view.
                ->default(false)
                ->queries(
                    true: fn (Builder $query) => $query->whereNotNull('closed_at'),
                    false: fn (Builder $query) => $query->whereNull('closed_at'),
                    blank: fn (Builder $query) => $query,
                ),
            SelectFilter::make('payment_status')
                ->label(__('lunar-filament::order.table.payment_status.label'))
                ->options([
                    'pending' => __('lunar::states.payment.pending'),
                    'authorized' => __('lunar::states.payment.authorized'),
                    'partially-paid' => __('lunar::states.payment.partially-paid'),
                    'paid' => __('lunar::states.payment.paid'),
                    'partially-refunded' => __('lunar::states.payment.partially-refunded'),
                    'refunded' => __('lunar::states.payment.refunded'),
                    'voided' => __('lunar::states.payment.voided'),
                ])
                ->multiple(),
            SelectFilter::make('fulfilment_status')
                ->label(__('lunar-filament::order.table.fulfilment_status.label'))
                ->options([
                    'unfulfilled' => __('lunar::states.fulfilment-status.unfulfilled'),
                    'partially-fulfilled' => __('lunar::states.fulfilment-status.partially-fulfilled'),
                    'fulfilled' => __('lunar::states.fulfilment-status.fulfilled'),
                    'partially-returned' => __('lunar::states.fulfilment-status.partially-returned'),
                    'returned' => __('lunar::states.fulfilment-status.returned'),
                ])
                ->multiple(),
            Filter::make('placed_at')
                ->schema([
                    DatePicker::make('placed_after')
                        ->label(__('lunar-filament::order.table.placed_after.label')),
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
