<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\DiscountResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\DiscountTypes\BuyXGetY;
use Lunar\Models\Currency;

class EditDiscount extends BaseEditRecord
{
    protected static string $resource = DiscountResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $minPrices = $data['data']['min_prices'] ?? [];
        $fixedPrices = $data['data']['fixed_values'] ?? [];
        $currencies = Currency::enabled()->get();

        foreach ($minPrices as $currencyCode => $value) {
            $currency = $currencies->first(
                fn ($currency) => $currency->code == $currencyCode
            );

            if (! $currency) {
                continue;
            }
            $data['data']['min_prices'][$currencyCode] = (int) round($value * $currency->factor);
        }

        foreach ($fixedPrices as $currencyCode => $fixedPrice) {
            $currency = $currencies->first(
                fn ($currency) => $currency->code == $currencyCode
            );

            if (! $currency) {
                continue;
            }
            $data['data']['fixed_values'][$currencyCode] = (int) round($fixedPrice * $currency->factor);
        }

        return $data;
    }

    public function getRelationManagers(): array
    {
        $managers = [];

        if ($this->record->type == BuyXGetY::class) {
            $managers[] = DiscountResource\RelationManagers\ProductConditionRelationManager::class;
            $managers[] = DiscountResource\RelationManagers\ProductRewardRelationManager::class;
        }

        return $managers;
    }
}
