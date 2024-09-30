<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Actions;
use Filament\Forms\Components\Grid;
use Filament\Resources\Components\Tab;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Pages\BaseListRecords;
use Lunar\Facades\DB;
use Lunar\Models\Attribute;
use Lunar\Models\Currency;
use Lunar\Models\Product;
use Lunar\Models\TaxClass;

class ListProducts extends BaseListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->createAnother(false)->form(
                static::createActionFormInputs()
            )->using(
                fn (array $data, string $model) => static::createRecord($data, $model)
            )->successRedirectUrl(fn (Model $record): string => route('filament.lunar.resources.products.edit', [
                'record' => $record,
            ])),
        ];
    }

    public static function createActionFormInputs(): array
    {
        return [
            Grid::make(2)->schema([
                ProductResource::getBaseNameFormComponent(),
                ProductResource::getProductTypeFormComponent()->required(),
            ]),
            Grid::make(2)->schema([
                ProductResource::getSkuFormComponent(),
                ProductResource::getBasePriceFormComponent(),
            ]),
        ];
    }

    public static function createRecord(array $data, string $model): Model
    {
        $currency = Currency::modelClass()::getDefault();

        $nameAttribute = Attribute::whereAttributeType(
            $model::morphName()
        )
            ->whereHandle('name')
            ->first()
            ->type;

        DB::beginTransaction();
        $product = $model::create([
            'status' => 'draft',
            'product_type_id' => $data['product_type_id'],
            'attribute_data' => [
                'name' => new $nameAttribute($data['name']),
            ],
        ]);
        $variant = $product->variants()->create([
            'tax_class_id' => TaxClass::modelClass()::getDefault()->id,
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
            'all' => Tab::make('All'),
            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'published')),
            'draft' => Tab::make('Draft')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft'))
                ->badge(Product::modelClass()::query()->where('status', 'draft')->count()),
        ];
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
}
