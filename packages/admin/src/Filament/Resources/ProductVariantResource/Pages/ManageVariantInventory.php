<?php

namespace Lunar\Admin\Filament\Resources\ProductVariantResource\Pages;

use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Filament\Resources\ProductVariantResource;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Core\Enums\StockMovementType;
use Lunar\Core\Models\Location;
use Lunar\Filament\Schemas\ProductVariant\ProductVariantForm;

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

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return LunarPanel::usesInventoryControls();
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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['stock'] = $this->getRecord()->stock_on_hand;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Stock is ledger-derived — reconcile on_hand at the default location
        // with a movement rather than writing a column.
        $targetOnHand = (int) ($data['stock'] ?? $record->stock_on_hand);
        unset($data['stock']);

        $record->update($data);

        $delta = $targetOnHand - $record->stock_on_hand;

        if ($delta !== 0) {
            $record->adjustStock(Location::getDefault(), $delta, StockMovementType::Adjustment);
        }

        return $record;
    }

    public function getDefaultForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                ProductVariantForm::getStockComponent(),
                ProductVariantForm::getPurchasableComponent()->live(),
                // Backorder is only consulted by the in_stock_or_on_backorder policy.
                // Disable (rather than hide) when it doesn't apply, so the field
                // stays in place and the layout doesn't reflow.
                ProductVariantForm::getBackorderComponent()
                    ->disabled(fn (Get $get): bool => $get('purchasable') !== 'in_stock_or_on_backorder'),
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
