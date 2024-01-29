<?php

namespace Lunar\Admin\Filament\Resources\ProductVariantResource\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;
use Lunar\Admin\Filament\Resources\ProductVariantResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class ManageVariantInventory extends BaseEditRecord
{
    protected static string $resource = ProductVariantResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('lunarpanel::productvariant.pages.inventory.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::productvariant.pages.inventory.title');
    }

    public function getBreadcrumbs(): array
    {
        return [
            ...ProductVariantResource::getBaseBreadcrumbs(
                $this->getRecord()
            ),
            ProductVariantResource::getUrl('inventory', [
                'record' => $this->getRecord(),
            ]) => $this->getTitle(),
        ];
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::product-inventory');
    }

    protected function getDefaultHeaderActions(): array
    {
        return [
            ProductVariantResource::getVariantSwitcherWidget(
                $this->getRecord()
            ),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                ProductVariantResource::getStockFormComponent(),
                ProductVariantResource::getBackorderFormComponent(),
                ProductVariantResource::getPurchasableFormComponent(),
                ProductVariantResource::getUnitQtyFormComponent(),
                ProductVariantResource::getQuantityIncrementFormComponent(),
                ProductVariantResource::getMinQuantityFormComponent(),
            ])->columns([
                'sm' => 1,
                'xl' => 3,
            ]),
        ]);
    }

    public static function getVariantSwitcherWidget(Model $record): Action
    {
        return Action::make('switch_variant')
            ->label(
                __('lunarpanel::widgets.variant_switcher.label')
            )
            ->modalContent(function () use ($record) {
                return view('lunarpanel::actions.switch-variant', [
                    'record' => $record->product,
                ]);
            })
            ->slideOver();
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
