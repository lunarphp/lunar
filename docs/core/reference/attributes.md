# Attributes

## Overview

Attributes can be associated to Eloquent models to allow custom data to be stored. Typically, these will be used the most with Products where different information is needed to be stored and presented to visitors.

For example, a television might have the following attributes assigned...

* Screen Size
* Screen Technology
* Tuner
* Resolution

## Models 

### Attributes

```php
Lunar\Models\Attribute
```

|Field|Description|
|:-|:-|
|`attribute_type`|Model type that can use attribute, e.g. `Lunar\Models\Product`|
|`attribute_group_id`|Attributes must associated to a group|
|`position`|An integer used to define the sorting order of attributes within attribute groups|
|`name`|Laravel Collection of translations `{'en': 'Screen Size'}`|
|`handle`|Kebab-cased reference, e.g. `screen-size`|
|`section`|An optional name to define where an attribute should be used.|
|`type`|The field type to be used, e.g. `Lunar\FieldTypes\Number`|
|`required`|Boolean|
|`default_value`||
|`configuration`|Meta data stored as a Laravel Collection|
|`system`|If set to true, indicates it should not be deleted|
|`validation_rules`||
|`filterable`||
|`searchable`||

### Attribute Groups

Attribute Groups form a collection of attributes that are logically grouped together for display purposes.

A good example might be a "Details" attribute group which has attributes for "Name" and "Description" or a "SEO" attribute group which has attributes for "Meta Title" and "Meta Description".

```php
Lunar\Models\AttributeGroup
```

|Field|Description|
|:-|:-|
|`attributable_type`|Model type the use the attribute group, e.g. `Lunar\Models\Product`|
|`name`|Laravel Collection of translations `{'en': 'SEO'}`|
|`handle`|Kebab-cased reference, e.g. `seo`|
|`position`|An integer used to define the sorting order of groups|

### Field Types

|Type|Config|
|:-|:-|
|`Lunar\FieldTypes\Number`|Integer or Decimal|
|`Lunar\FieldTypes\Text`|Single-line, Multi-line, Rich Text|
|`Lunar\FieldTypes\TranslatedText`|Single-line, Multi-line, Rich Text|
|`Lunar\FieldTypes\ListField`|An re-orderable list of text values|

::: tip INFO
More field types will be coming soon.
:::

## Models that use Attributes by default

* Lunar\Models\Brand
* Lunar\Models\Collection
* Lunar\Models\Customer
* Lunar\Models\Product
* Lunar\Models\ProductVariant

## Saving Attribute Data

```php
$product->attribute_data = collect([
    'meta_title' => new \Lunar\FieldTypes\Text('The best screwdriver you will ever buy!'),
    'pack_qty' => new \Lunar\FieldTypes\Number(2),
    'description' => new \Lunar\FieldTypes\TranslatedText(collect([
        'en' => new \Lunar\FieldTypes\Text('Blue'),
        'fr' => new \Lunar\FieldTypes\Text('Bleu'),
    ])),
]);
```


## Accessing Attribute Data.

There will come times where you need to be able to retrieve the attribute data you have stored against a model. When you target the `attribute_data` property it will be cast as a collection and resolved into it's corresponding field type.

```php
dump($product->attribute_data);

Illuminate\Support\Collection {#1522 ▼
  #items: array:2 [▼
    "name" => Lunar\FieldTypes\TranslatedText {#1533 ▼
      #value: Illuminate\Support\Collection {#1505 ▼
        #items: array:3 [▼
          "de" => Lunar\FieldTypes\Text {#1506 ▼
            #value: "Leren laarzen"
          }
          "en" => Lunar\FieldTypes\Text {#1514 ▼
            #value: "Leather boots"
          }
          "fr" => Lunar\FieldTypes\Text {#1502 ▼
            #value: "Bottes en cuires"
          }
        ]
      }
    }
    "description" => Lunar\FieldTypes\Text {#1537 ▼
      #value: "<p>I'm a description!</p>"
    }
  ]
}
```

If you need to just get the value for one field, you can use the `translateAttribute` method on the model:

```php
// Leather boots
$product->translateAttribute('name');

// Bootes en cuires
$product->translateAttribute('name', 'fr');

// Leather boots
$product->translateAttribute('name', 'FOO');
// We will default here to either the current system locale or the first value available.
```
## Mapped attributes

The `mappedAttributes()` method in the `HasAttributes` trait defines a `HasMany` relation between the model with attribute data and the defined attributes.

::: warning
It is possible to [store](#saving-attribute-data) and [access](#saving-attribute-data) `attribute_data` without to define the relating `Attribute` models, but with various restrictions, e.G. they won't be available in admin hub and will not indexed from [Search](/core/reference/search) engine by default.
:::

::: tip
When loading models it is advised you eager load the `mappedAttributes` relation if required.
:::

## Adding attributes to your own model

```php
use Lunar\Base\Traits\HasAttributes;

class SomeModel extends Model
{
    use HasAttributes;
    //...
}

```

Then ensure you have a JSON field on your model's migration files called `attribute_data`:

```php
//...
$this->json('attribute_data');
//...
```

or simply

```php
//...
$this->attributeData();
//...
```

::: warning
The attributes of your own model will not be available in admin hub by default.
:::