<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Filament\Resources\ProductVariantResource\Pages\ManageVariantInventory;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Core\Enums\StockMovementType;
use Lunar\Core\Models\Contracts\ProductVariant as ProductVariantContract;
use Lunar\Core\Models\Location;

class ManageProductInventory extends BaseEditRecord
{
    protected static string $resource = ProductResource::class;

    public ?string $stock = null;

    public ?string $backorder = null;

    public ?string $purchasable = null;

    public ?int $unit_quantity = 1;

    public ?int $quantity_increment = 1;

    public ?int $min_quantity = 1;

    public function getTitle(): string|Htmlable
    {
        return __('lunarpanel::product.pages.inventory.label');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::product.pages.inventory.label');
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        if (! LunarPanel::usesInventoryControls()) {
            return false;
        }

        return ($parameters['record']->variants_count ?? $parameters['record']->variants()->count()) == 1;
    }

    public function getBreadcrumb(): string
    {
        return __('lunarpanel::product.pages.inventory.label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::product-inventory');
    }

    protected function getDefaultHeaderActions(): array
    {
        return [];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $variant = $this->getVariant();

        $this->stock = $variant->stock_on_hand;
        $this->backorder = $variant->backorder;
        $this->purchasable = $variant->purchasable;
        $this->unit_quantity = $variant->unit_quantity;
        $this->min_quantity = $variant->min_quantity;
        $this->quantity_increment = $variant->quantity_increment;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $variant = $this->getVariant();

        // Stock is ledger-derived — reconcile on_hand at the default location
        // with a movement rather than writing a column.
        $targetOnHand = (int) ($data['stock'] ?? $variant->stock_on_hand);
        unset($data['stock']);

        $variant->update($data);

        $delta = $targetOnHand - $variant->stock_on_hand;

        if ($delta !== 0) {
            $variant->adjustStock(Location::getDefault(), $delta, StockMovementType::Adjustment);
        }

        return $record;
    }

    protected function getVariant(): ProductVariantContract
    {
        return $this->getRecord()->variants()->first();
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
        ];
    }

    public function getDefaultForm(Schema $schema): Schema
    {
        return (new ManageVariantInventory)->form($schema)->statePath('');
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
