<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\DiscountResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;
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
        $currencies = Currency::get();

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

        return parent::mutateFormDataBeforeSave($data);
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
