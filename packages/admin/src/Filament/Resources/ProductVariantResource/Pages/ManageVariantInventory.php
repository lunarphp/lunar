<?php

namespace Lunar\Admin\Filament\Resources\ProductVariantResource\Pages;

use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Filament\Resources\ProductVariantResource;
use Lunar\Admin\Filament\Resources\ProductVariantResource\Schemas\ProductVariantForm;
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

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->url(function (Model $record) {
            return ProductResource::getUrl('variants', [
                'record' => $record->product,
            ]);
        });
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

    public function getDefaultForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                ProductVariantForm::getStockComponent(),
                ProductVariantForm::getBackorderComponent(),
                ProductVariantForm::getPurchasableComponent(),
                ProductVariantForm::getUnitQtyComponent(),
                ProductVariantForm::getQuantityIncrementComponent(),
                ProductVariantForm::getMinQuantityComponent(),
            ])->columns([
                'sm' => 1,
                'xl' => 3,
            ]),
        ]);
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
