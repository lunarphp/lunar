<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Core\Models\ProductVariant;
use Lunar\Filament\Actions\Products\AdjustStockAction;
use Lunar\Filament\Schemas\ProductVariant\ProductVariantInventory;

/**
 * Single-variant convenience: the variant's inventory controls surfaced on the
 * product. The full per-variant view lives on the variant Inventory page; this
 * mirrors the editable selling-policy fields, a read-only rollup summary, and
 * the AdjustStock action.
 */
class ManageProductInventory extends BaseEditRecord
{
    protected static string $resource = ProductResource::class;

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

        $this->backorder = $variant->backorder;
        $this->purchasable = $variant->purchasable;
        $this->unit_quantity = $variant->unit_quantity;
        $this->min_quantity = $variant->min_quantity;
        $this->quantity_increment = $variant->quantity_increment;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $this->getVariant()->update($data);

        return $record;
    }

    protected function getVariant(): ProductVariant
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
        return $schema->components([
            Section::make(__('lunar-filament::productvariant.inventory.summary_heading'))
                ->description(ProductVariantInventory::locationDescription())
                ->headerActions([
                    AdjustStockAction::make()->record($this->getVariant()),
                ])
                ->schema([
                    $this->rollupPlaceholder('stock_on_hand', 'on_hand'),
                    $this->rollupPlaceholder('stock_available', 'available'),
                    $this->rollupPlaceholder('stock_committed', 'committed'),
                    $this->rollupPlaceholder('stock_reserved', 'reserved'),
                ])
                ->columns(['default' => 2, 'xl' => 4]),
            ProductVariantInventory::getSellingPolicySection(),
        ])->statePath('');
    }

    protected function rollupPlaceholder(string $attribute, string $key): Placeholder
    {
        return Placeholder::make($attribute)
            ->label(__("lunar-filament::productvariant.inventory.{$key}"))
            ->content(fn (): string => (string) ($this->getVariant()->{$attribute} ?? 0));
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
