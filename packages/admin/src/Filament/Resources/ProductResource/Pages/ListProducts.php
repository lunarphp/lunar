<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Pages\BaseListRecords;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\TaxClass;
use Lunar\Filament\Schemas\Product\ProductForm;

class ListProducts extends BaseListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            CreateAction::make()->createAnother(false)->schema(
                static::createActionFormInputs()
            )->using(
                fn (array $data, string $model) => static::createRecord($data, $model)
            )->successRedirectUrl(fn (Model $record): string => ProductResource::getUrl('edit', [
                'record' => $record,
            ])),
        ];
    }

    public static function createActionFormInputs(): array
    {
        return [
            Grid::make(2)->schema([
                ProductForm::getBaseNameComponent(),
                ProductForm::getProductTypeComponent()->required(),
            ]),
            Grid::make(2)->schema([
                ProductForm::getSkuComponent(),
                ProductForm::getBasePriceComponent(),
            ]),
        ];
    }

    public static function createRecord(array $data, string $model): Model
    {
        $currency = Currency::getDefault();

        DB::beginTransaction();
        $product = $model::create([
            'status' => 'draft',
            'product_type_id' => $data['product_type_id'],
            'name' => $data['name'],
        ]);
        $variant = $product->variants()->create([
            'tax_class_id' => TaxClass::getDefault()->id,
            'sku' => $data['sku'],
        ]);
        $variant->prices()->create([
            'min_quantity' => 1,
            'currency_id' => $currency->id,
            'price' => (int) bcmul($data['base_price'], $currency->factor),
        ]);
        DB::commit();

        return $product;
    }

    public function getDefaultTabs(): array
    {
        return [
            'all' => Tab::make(__('lunarpanel::product.tabs.all')),
            'published' => Tab::make(__('lunarpanel::product.tabs.published'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'published')),
            'draft' => Tab::make(__('lunarpanel::product.tabs.draft'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft'))
                ->badge(Product::query()->where('status', 'draft')->count()),
            'archived' => Tab::make(__('lunarpanel::product.tabs.archived'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'archived')),
        ];
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
