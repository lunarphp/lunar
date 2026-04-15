<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;
use Lunar\Facades\ModelManifest;
use Lunar\Models\Address;
use Lunar\Models\Asset;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Brand;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\CartLine;
use Lunar\Models\Channel;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Discount;
use Lunar\Models\Discountable;
use Lunar\Models\DiscountCollection;
use Lunar\Models\Language;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;
use Lunar\Models\OrderLine;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductAssociation;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;
use Lunar\Models\State;
use Lunar\Models\Tag;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRate;
use Lunar\Models\TaxRateAmount;
use Lunar\Models\TaxZone;
use Lunar\Models\TaxZoneCountry;
use Lunar\Models\TaxZoneCustomerGroup;
use Lunar\Models\TaxZonePostcode;
use Lunar\Models\TaxZoneState;
use Lunar\Models\Transaction;
use Lunar\Models\Url;
use Lunar\Models\UserPermission;

class RemapPolymorphicRelations extends Migration
{
    public function up()
    {
        $modelClasses = collect([
            CartLine::class,
            ProductOption::class,
            Asset::class,
            Brand::class,
            TaxZone::class,
            TaxZoneCountry::class,
            TaxZoneCustomerGroup::class,
            DiscountCollection::class,
            TaxClass::class,
            ProductOptionValue::class,
            Channel::class,
            AttributeGroup::class,
            Tag::class,
            Cart::class,
            Collection::class,
            Discount::class,
            TaxRate::class,
            Price::class,
            Discountable::class,
            State::class,
            UserPermission::class,
            OrderAddress::class,
            Country::class,
            Address::class,
            Url::class,
            ProductVariant::class,
            TaxZonePostcode::class,
            ProductAssociation::class,
            TaxRateAmount::class,
            Attribute::class,
            Order::class,
            Customer::class,
            OrderLine::class,
            CartAddress::class,
            Language::class,
            TaxZoneState::class,
            Currency::class,
            Product::class,
            Transaction::class,
            ProductType::class,
            CollectionGroup::class,
            CustomerGroup::class,
        ])->mapWithKeys(
            fn ($class) => [
                $class => ModelManifest::getMorphMapKey($class),
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
                DB::table($table)
                    ->where($column, '=', $modelClass)
                    ->update([
                        $column => $mapping,
                    ]);
            }

            foreach ($tables as $tableName => $columns) {
                $table = DB::table(
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
