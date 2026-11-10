<?php

namespace Lunar\Admin\Filament\Resources\ProductVariantResource\Pages;

use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Events\ProductVariantInventoryUpdated;
use Lunar\Admin\Filament\Resources\ProductResource;
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

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record = parent::handleRecordUpdate($record, $data);

        ProductVariantInventoryUpdated::dispatch($record);

        return $record;
    }

    public function getDefaultForm(Schema $schema): Schema
    {
        return $schema->components([
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

    public function getRelationManagers(): array
    {
        return [];
    }
}
