<?php

use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class RemapPolymorphicRelations extends Migration
{
    public function up()
    {
        $modelClasses = collect([
            \Lunar\Models\CartLine::class,
            \Lunar\Models\ProductOption::class,
            \Lunar\Models\Asset::class,
            \Lunar\Models\Brand::class,
            \Lunar\Models\TaxZone::class,
            \Lunar\Models\TaxZoneCountry::class,
            \Lunar\Models\TaxZoneCustomerGroup::class,
            \Lunar\Models\DiscountCollection::class,
            \Lunar\Models\TaxClass::class,
            \Lunar\Models\ProductOptionValue::class,
            \Lunar\Models\Channel::class,
            \Lunar\Models\AttributeGroup::class,
            \Lunar\Models\Tag::class,
            \Lunar\Models\Cart::class,
            \Lunar\Models\Collection::class,
            \Lunar\Models\Discount::class,
            \Lunar\Models\TaxRate::class,
            \Lunar\Models\Price::class,
            \Lunar\Models\Discountable::class,
            \Lunar\Models\State::class,
            \Lunar\Models\UserPermission::class,
            \Lunar\Models\OrderAddress::class,
            \Lunar\Models\Country::class,
            \Lunar\Models\Address::class,
            \Lunar\Models\Url::class,
            \Lunar\Models\ProductVariant::class,
            \Lunar\Models\TaxZonePostcode::class,
            \Lunar\Models\ProductAssociation::class,
            \Lunar\Models\TaxRateAmount::class,
            \Lunar\Models\Attribute::class,
            \Lunar\Models\Order::class,
            \Lunar\Models\Customer::class,
            \Lunar\Models\OrderLine::class,
            \Lunar\Models\CartAddress::class,
            \Lunar\Models\Language::class,
            \Lunar\Models\TaxZoneState::class,
            \Lunar\Models\Currency::class,
            \Lunar\Models\Product::class,
            \Lunar\Models\Transaction::class,
            \Lunar\Models\ProductType::class,
            \Lunar\Models\CollectionGroup::class,
            \Lunar\Models\CustomerGroup::class,
        ])->mapWithKeys(
            fn ($class) => [
                $class => \Lunar\Facades\ModelManifest::getMorphMapKey($class),
            ]
        );

        $tables = [
            'attributables' => ['attributable_type'],
            'attributes' => ['attribute_type'],
            'attribute_groups' => ['attributable_type'],
            'cart_lines' => ['purchasable_type'],
            'channelables' => ['channelable_type'],
            'discount_purchasables' => ['purchasable_type'],
            'order_lines' => ['purchasable_type'],
            'prices' => ['priceable_type'],
            'taggables' => ['taggable_type'],
            'urls' => ['element_type'],
        ];

        $nonLunarTables = [
            'activity_log' => 'subject_type',
            'media' => 'model_type',
            'model_has_permissions' => 'model_type',
            'model_has_roles' => 'model_type',
        ];

        foreach ($modelClasses as $modelClass => $mapping) {

            foreach ($nonLunarTables as $table => $column) {
                if (! Schema::hasTable($table)) {
                    continue;
                }
                \Illuminate\Support\Facades\DB::table($table)
                    ->where($column, '=', $modelClass)
                    ->update([
                        $column => $mapping,
                    ]);
            }

            foreach ($tables as $tableName => $columns) {
                $table = \Illuminate\Support\Facades\DB::table(
                    $this->prefix.$tableName
                );

                foreach ($columns as $column) {
                    $table->where($column, '=', $modelClass)->update([
                        $column => $mapping,
                    ]);
                }
            }
        }
    }

    public function down()
    {
        // ...
    }
}
