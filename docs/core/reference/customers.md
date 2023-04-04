# Customers

## Overview

We use Customers in Lunar to store the customer details, rather than Users. We do this for a few reasons. One, so that we leave your User models well alone and two, because it provides flexibility.

## Customers

```php
Lunar\Models\Customer
```

|Field|Description|
|:-|:-|
|`id`||
|`title`|Mr, Mrs, Miss, etc|
|`first_name`||
|`last_name`||
|`company_name`|nullable|
|`vat_no`|nullable|
|`account_ref`|nullable|
|`attribute_data`|JSON|
|`meta`|JSON|
|`created_at`||
|`updated_at`||

### Creating a customer

```php
Lunar\Models\Customer::create([
    'title' => 'Mr.',
    'first_name' => 'Tony',
    'last_name' => 'Stark',
    'company_name' => 'Stark Enterprises',
    'vat_no' => null,
    'meta' => [
        'account_no' => 'TNYSTRK1234'
    ],
])
```

### Relationships

- Customer Groups `customer_customer_group`
- Users - `customer_user`

## Users

Customers will typically be associated with a user, so they can place orders. But it is also possible to have multiple users associated with a customer. This can be useful on B2B e-commerce where a customer may have multiple buyers.

### Attaching users to a customer

```php
$customer = \Lunar\Models\Customer::create([/* ... */]);

$customer->users()->attach($user);

$customer->users()->sync([1,2,3]);
```

## Attaching a customer to a customer group

```php
$customer = \Lunar\Models\Customer::create([/* ... */]);

$customer->customerGroups()->attach($customerGroup);

$customer->customerGroups()->sync([4,5,6]);
```

## Impersonating users

When a customer needs help with their account, it's useful to be able to log in as that user so you can help diagnose the issue they're having. 
Lunar allows you to specify your own method of how you want to impersonate users, usually this is in the form of a signed URL an admin can go to in order to log in as the user.

### Creating the impersonate class

```php
<?php

namespace App\Auth;

use Lunar\Hub\Auth\Impersonate as LunarImpersonate;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\URL;

class Impersonate extends LunarImpersonate
{
    /**
     * Return the URL for impersonation.
     *
     * @return string
     */
    public function getUrl(Authenticatable $authenticatable): string
    {
        return URL::temporarySignedRoute('impersonate.link', now()->addMinutes(5), [
            'user' => $authenticatable->getAuthIdentifier(),
        ]);
    }
}
```

Then you need to register this in `config/lunar-hub/customers.php`.

```php
return [
    'impersonate' => App\Auth\Impersonate::class,
    // ...
];
```

Once added you will see an option to impersonate the user when viewing a customer. This will then go to the URL specified in your class where you will be able to handle the impersonation logic.

## Customer Groups

Default `retail`

Customer groups allow you to group your customers into logical segments which enables you to define different criteria on models based on what customer belongs to that group.

These criteria include things like:

### Pricing

Specify different pricing per customer group, for example you may have certain prices for customers that are in the `trade` customer group.

### Product Availability

You can turn product visibility off depending on the customer group, this would mean only certain products would show depending on the group they belong to. This will also include scheduling availability so you can release products earlier or later to different groups.

---
You must have at least one customer group in your store and when you install Lunar you will be given a default one to get you started named `retail`.

## Creating a customer group

```php
$customerGroup = Lunar\Models\CustomerGroup::create([
    'name' => 'Retail',
    'handle' => 'retail', // Must be unique
    'default' => false,
]);
```

::: tip
You can only have one default at a time, if you create a customer group and pass default to true, then the existing default will be set to `false`.
:::

## Scheduling availability

If you would like to add customer group availability to your own models, you can use the `HasCustomerGroups` trait.

```php

// ...
use Lunar\Base\Traits\HasCustomerGroups;

class MyModel extends Model
{
    use HasCustomerGroups;
}
```

You will need to define the relationship for customer groups so that Lunar knows how to handle it.

```php
public function customerGroup()
{
    return $this->hasMany(\Lunar\Models\CustomerGroup::class)->withTimestamps()->withPivot([/* .. */]);
}
```
You will then have access to the following methods:

### Scheduling customer groups

```php
// Will schedule for this product to be enabled in 14 days for this customer group.
$myModel->scheduleCustomerGroup(
    $customerGroup,
    $startDate,
    $endData,
    $pivotData
);

// Schedule the product to be enabled straight away
$myModel->scheduleCustomerGroup($customerGroup);

// The schedule method will accept and array or collection of customer groups.
$myModel->scheduleCustomerGroup(CustomerGroup::get());
```

### Unscheduling customer groups

If you do not want a model to be enabled for a customer group, you can unschedule it. This will keep any previous `start` and `end` dates but will toggle the `enabled` column.

```php
$myModel->unscheduleCustomerGroup($customerGroup, $pivotData);
```

### Parameters

|Field|Description|Type|Default|
|:-|:-|:-|:-|
|`customerGroup`|A collection of CustomerGroup models or id's.|mixed|
|`startDate`|The date the customer group will be active from.|`DateTime`|
|`endDate`|The date the customer group will be active until.|`DateTime`|
|`pivotData`|Any additional pivot data you may have on your link table. (not including scheduling defaults)|`array`|


**Pivot Data**

By default the following values are used for `$pivotData`

- `enabled` - Whether the customer group is enabled, defaults to `true` when scheduling and `false` when unscheduling.

You can override any of these yourself as they are merged behind the scenes.

### Retrieving the relationship

The `HasCustomerGroup` trait adds a `customerGroup` scope to the model. This lets you query based on availability for a specific or multiple customer groups.

The scope will accept either a single ID or instance of `CustomerGroup` and will accept accept an array.

```php
$results = MyModel::customerGroup(1, $startDate, $endDate)->paginate();

$results = MyModel::customerGroup([
    $groupA,
    $groupB,
])->paginate(50);
```

The start and end dates should be `DateTime` objects with will query for the existence of a customer group association with the start and end dates between those given. These are optional and the following happens in certain situations:

**Pass neither `startDate` or `endDate`**

Will query for customer groups which are enabled and the `startDate` is after `now()`

**Pass only `startDate`**

Will query for customer groups which are enabled, the start date is after the given date and the end date is either null or before `now()`

**Pass both `startDate` and `endDate`**

Will query for customer groups which are enabled, the start date is after the given date and the end date is before the given date.

**Pass `endDate` without `startDate`**

Will query for customer groups which are enabled, the start date is after the `now()` and the end date is before the given date.

If you omit the second parameter the scope will take the current date and time.

::: tip
A model will only be returned if the `enabled` column is positive, regardless of whether the start and end dates match.
:::
