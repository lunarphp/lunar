<?php

namespace Lunar\Filament\Actions\Products;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\Width;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\ProductVariant;

/**
 * Sets the stock the default location expects to receive. A purchasing add-on
 * that owns `incoming` hides it, for example with
 * `SetIncomingStockAction::configureUsing(fn ($action) => $action->hidden())`.
 */
class SetIncomingStockAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'set_incoming_stock';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('lunar-filament::actions.products.set_incoming_stock.label'))
            ->modalHeading(__('lunar-filament::actions.products.set_incoming_stock.modal_heading'))
            ->modalWidth(Width::Medium)
            ->icon('heroicon-o-truck')
            ->hidden(fn (): bool => ! Location::getDefault())
            ->fillForm(fn (ProductVariant $record): array => [
                'incoming' => (int) ($record->stockLevels()
                    ->where('location_id', Location::getDefault()?->getKey())
                    ->value('incoming') ?? 0),
            ])
            ->schema([
                TextInput::make('incoming')
                    ->label(__('lunar-filament::actions.products.set_incoming_stock.fields.incoming.label'))
                    ->helperText(__('lunar-filament::actions.products.set_incoming_stock.fields.incoming.helper_text'))
                    ->required()
                    ->integer()
                    ->minValue(0),
            ])
            ->action(fn (array $data, ProductVariant $record) => $record->setIncomingStock(
                Location::getDefault(),
                (int) $data['incoming'],
            ))
            ->successNotificationTitle(__('lunar-filament::actions.products.set_incoming_stock.notification.success'));
    }
}
