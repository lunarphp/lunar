<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Base\Migration;
use Lunar\Core\Facades\ModelManifest;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\Asset;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Discountable;
use Lunar\Core\Models\DiscountCollection;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderAddress;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductAssociation;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductOptionValue;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\State;
use Lunar\Core\Models\Tag;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Models\TaxRate;
use Lunar\Core\Models\TaxRateAmount;
use Lunar\Core\Models\TaxZone;
use Lunar\Core\Models\TaxZoneCountry;
use Lunar\Core\Models\TaxZoneCustomerGroup;
use Lunar\Core\Models\TaxZonePostcode;
use Lunar\Core\Models\TaxZoneState;
use Lunar\Core\Models\Transaction;
use Lunar\Core\Models\Url;
use Lunar\Core\Models\UserPermission;

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
