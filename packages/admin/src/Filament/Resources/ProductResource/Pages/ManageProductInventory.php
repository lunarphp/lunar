<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Models\ProductVariant;

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
        return $parameters['record']->variants()->count() == 1;
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

        $this->stock = $variant->stock;
        $this->backorder = $variant->backorder;
        $this->purchasable = $variant->purchasable;
        $this->unit_quantity = $variant->unit_quantity;
        $this->min_quantity = $variant->min_quantity;
        $this->quantity_increment = $variant->quantity_increment;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $variant = $this->getVariant();

        $variant->update($data);

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

    public function form(Form $form): Form
    {
        $variant = $this->getVariant();

        return $form->schema([
            Section::make()->schema([
                TextInput::make('stock')
                    ->label(
                        __('lunarpanel::product.pages.inventory.form.stock.label')
                    )->numeric(),
                TextInput::make('backorder')
                    ->label(
                        __('lunarpanel::product.pages.inventory.form.backorder.label')
                    )->numeric(),
                Select::make('purchasable')
                    ->options([
                        'always' => __('lunarpanel::product.pages.inventory.form.purchasable.options.always'),
                        'in_stock' => __('lunarpanel::product.pages.inventory.form.purchasable.options.in_stock'),
                        'backorder' => __('lunarpanel::product.pages.inventory.form.purchasable.options.backorder'),
                    ])
                    ->label(
                        __('lunarpanel::product.pages.inventory.form.purchasable.label')
                    ),
                TextInput::make('unit_quantity')
                    ->label(
                        __('lunarpanel::product.pages.inventory.form.unit_quantity.label')
                    )->helperText(
                        __('lunarpanel::product.pages.inventory.form.unit_quantity.helper_text')
                    )->numeric(),
                TextInput::make('quantity_increment')
                    ->label(
                        __('lunarpanel::product.pages.inventory.form.quantity_increment.label')
                    )->helperText(
                        __('lunarpanel::product.pages.inventory.form.quantity_increment.helper_text')
                    )->numeric(),
                TextInput::make('min_quantity')
                    ->label(
                        __('lunarpanel::product.pages.inventory.form.min_quantity.label')
                    )->helperText(
                        __('lunarpanel::product.pages.inventory.form.min_quantity.helper_text')
                    )->numeric(),
            ])->columns([
                'sm' => 1,
                'xl' => 3,
            ]),
        ])->statePath('');
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
