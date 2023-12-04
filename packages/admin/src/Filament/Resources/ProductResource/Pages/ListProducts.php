<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Filament\Actions;
use Filament\Forms\Components\Grid;
use Filament\Resources\Components\Tab;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Pages\BaseListRecords;
use Lunar\Facades\DB;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Attribute;
use Lunar\Models\Currency;
use Lunar\Models\Language;
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
        $language = Language::getDefault();
        $currency = Currency::getDefault();

        $nameAttribute = Attribute::whereAttributeType($model)
            ->whereHandle('name')
            ->first();

        $name = $data['name'];

        if ($nameAttribute->type == TranslatedText::class) {
            $name = [$language->code => $name];
        }

        DB::beginTransaction();
        $product = $model::create([
            'status' => 'draft',
            'product_type_id' => $data['product_type_id'],
            'attribute_data' => [
                'name' => new $nameAttribute->type($name),
            ],
        ]);
        $variant = $product->variants()->create([
            'tax_class_id' => TaxClass::getDefault()->id,
            'sku' => $data['sku'],
        ]);
        $variant->prices()->create([
            'tier' => 1,
            'price' => (int) bcmul($data['base_price'], $currency->factor),
        ]);
        DB::commit();

        return $product;
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'published')),
            'draft' => Tab::make('Draft')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft'))
                ->badge(Product::query()->where('status', 'draft')->count()),
        ];
    }

    protected function paginateTableQuery(Builder $query): Paginator
    {
        return $query->simplePaginate($this->getTableRecordsPerPage());
    }
}
