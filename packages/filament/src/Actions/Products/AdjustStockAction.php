<?php

namespace Lunar\Filament\Actions\Products;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\Width;
use Lunar\Core\Contracts\Actions\Products\AdjustsStock;
use Lunar\Core\Models\ProductVariant;

class AdjustStockAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'adjust_stock';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('lunar-filament::actions.products.adjust_stock.label'))
            ->modalHeading(__('lunar-filament::actions.products.adjust_stock.modal_heading'))
            ->modalWidth(Width::Medium)
            ->icon('heroicon-o-archive-box-arrow-down')
            ->schema([
                TextInput::make('delta')
                    ->label(__('lunar-filament::actions.products.adjust_stock.fields.delta.label'))
                    ->helperText(__('lunar-filament::actions.products.adjust_stock.fields.delta.helper_text'))
                    ->required()
                    ->integer(),
                TextInput::make('reason')
                    ->label(__('lunar-filament::actions.products.adjust_stock.fields.reason.label'))
                    ->maxLength(255),
            ])
            ->action(fn (array $data, ProductVariant $record) => app(AdjustsStock::class)->execute(
                $record,
                (int) $data['delta'],
                $data['reason'] ?? null,
            ))
            ->successNotificationTitle(__('lunar-filament::actions.products.adjust_stock.notification.success'));
    }
}
