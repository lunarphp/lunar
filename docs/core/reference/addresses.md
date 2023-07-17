# Addresses

## Overview

When a registered customer looks to checkout it is helpful for them to be able to save their preferred shipping 
addresses. Lunar provides the `Address` Eloquent model which allows a developer to store addresses against a `Customer`
record for this very purpose.

It is worth noting that the `Address` model is **not** related to carts and orders. It is simply a facility to save address
information for reuse.

::: info
Carts and Orders have their own address models [`\Lunar\Models\CartAddress`](carts.html) and 
[`\Lunar\Models\OrderAddress`](orders.html) which you can learn more about in those sections.
:::

## Eloquent Models

The primary model for this facility is the `Address` model. We also have the `Country` and 
`State` models which come pre-populated with data when installing Lunar.

| Eloquent Model         | Description                            |                                                                                          |
|:-----------------------|:---------------------------------------|:-----------------------------------------------------------------------------------------|
| `Lunar\Models\Address` | Stores customer addresses.             |[view api](https://lunar-api-docs.staging-03.neondigital.co.uk/Lunar/Models/Address.html)|
| `Lunar\Models\Country` | Lookup for all countries in the world. |[view api](https://lunar-api-docs.staging-03.neondigital.co.uk/Lunar/Models/Country.html)|
| `Lunar\Models\State`   | Lookup for states per country.         |[view api](https://lunar-api-docs.staging-03.neondigital.co.uk/Lunar/Models/State.html)  |

## Address Data

Data for Countries and States is provided by https://github.com/dr5hn/countries-states-cities-database.

Although Lunar adds the address data automatically for you on installation, you may use the following command to import 
countries and states if required.

```sh
php artisan lunar:import:address-data
```
## Example Usage

### Creating An Address

```php
use Lunar\Models\Address;
use Lunar\Models\Country;
use Lunar\Models\State;

$country = Country::where('iso', 'GB')->first();  // E.g. United Kingdom
$state = State::find(4496); // E.g. Essex

$address = Address::create([
    'customer_id' => $customer->id,
    'title' => 'Mr',
    'first_name' => 'John',
    'last_name' => 'Smith',
    'company_name' => 'My Company Ltd', // optional
    'line_one' => '10 Example Street',
    'line_two' => 'My Village',
    'line_three' => '',
    'city' => 'Chelmsford',
    'state' => $state->name,
    'postcode' => 'CM1 2AB',
    'country_id' => $country->id,
    'delivery_instructions' => 'Leave by the side gate',
    'contact_email' => 'some@email.com',
    'contact_phone' => '07123 123456',
    'last_used_at' => null,
    'meta' => [
        'business' => true, // meta data is optional
    ],
    'shipping_default' => true,
    'billing_default' => false,
    'created_at' => now(),
    'updated_at' => now(),
]);
```

### Getting A Customer's Addresses

The example below assumes the authenticated user has been associated to a customer record.

```php
$customer = Auth::user()->latestCustomer();

foreach ($customer->addresses as $address) {
    // 
}
```

### Listing Countries

Lunar supplies country data, which includes ISO code, native country names and Emoji graphics :uk:.

```php
@foreach (Country::all() as $country)
    <option value="{{ $country->id }}">{{ $country->emoji }} {{ $country->native }}</option>
@endforeach
```

### Listing States

States relate to countries. You can easily load them via a country model using its relationship.

```php
@foreach ($country->states as $state)
    <option value="{{ $state->id }}">{{ $state->name }}</option>
@endforeach
```
