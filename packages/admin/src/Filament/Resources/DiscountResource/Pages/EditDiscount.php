<?php

namespace Lunar\Admin\Filament\Resources\DiscountResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationGroup;
use Lunar\Admin\Filament\Resources\DiscountResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Core\DiscountTypes\BuyXGetY;
use Lunar\Core\Facades\PriceCalculator;
use Lunar\Core\Models\Currency;
use Lunar\Filament\Contracts\DiscountFormType;
use Lunar\Filament\RelationManagers\Discount\CollectionConditionRelationManager;
use Lunar\Filament\RelationManagers\Discount\ProductConditionRelationManager;
use Lunar\Filament\RelationManagers\Discount\ProductRewardRelationManager;
use Lunar\Filament\Support\Facades\LunarFilament;

class EditDiscount extends BaseEditRecord
{
    protected static string $resource = DiscountResource::class;

    public function getTitle(): string
    {
        return __('lunarpanel::discount.pages.edit.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::discount.pages.edit.title');
    }

    protected function getDefaultHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($form = $this->typeForm($data['type'] ?? null)) {
            return $form->lunarPanelOnFill($data);
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($form = $this->typeForm($data['type'] ?? null)) {
            return $form->lunarPanelOnSave($data);
        }

        $minPrices = $data['data']['min_prices'] ?? [];
        $fixedPrices = $data['data']['amounts'] ?? [];
        $currencies = Currency::enabled()->get();

        foreach ($minPrices as $currencyCode => $value) {
            $currency = $currencies->first(
                fn ($currency) => $currency->code == $currencyCode
            );

            if (! $currency) {
                continue;
            }
            $data['data']['min_prices'][$currencyCode] = PriceCalculator::toMinor($value, $currency);
        }

        foreach ($fixedPrices as $currencyCode => $fixedPrice) {
            $currency = $currencies->first(
                fn ($currency) => $currency->code == $currencyCode
            );

            if (! $currency) {
                continue;
            }
            $data['data']['amounts'][$currencyCode] = PriceCalculator::toMinor($fixedPrice, $currency);
        }

        return $data;
    }

    public function getRelationManagers(): array
    {
        $managers = [];

        if ($this->record->type == BuyXGetY::class) {
            $managers[] = RelationGroup::make(__('lunarpanel::discount.form.conditions.heading'), [
                ProductConditionRelationManager::class,
                CollectionConditionRelationManager::class,
            ]);
            $managers[] = ProductRewardRelationManager::class;
        }

        if ($form = LunarFilament::discountFormFor($this->record->getType())) {
            $managers = array_merge($managers, $form->lunarPanelRelationManagers());
        }

        return $managers;
    }

    /** The Filament form for a stored type class, if the type contributes one. */
    protected function typeForm(?string $typeClass): ?DiscountFormType
    {
        if (! $typeClass || ! class_exists($typeClass)) {
            return null;
        }

        return LunarFilament::discountFormFor(new $typeClass);
    }
}
