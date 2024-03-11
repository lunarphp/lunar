<p align="center"><img src="https://github.com/lunarphp/lunar/assets/1488016/a21f1cfb-9259-4d21-9bb0-eca876957729" width="300" ></p>



<p align="center">This addon enables Opayo payments on your Lunar storefront.</p>

## Alpha Release

This addon is currently in Alpha, whilst every step is taken to ensure this is working as intended, it will not be considered out of Alpha until more tests have been added and proved.

## Minimum Requirements

- Lunar  `1.x`
- An [Elavon](https://www.elavon.com/) merchant account

## Installation

### Require the composer package

```sh
composer require lunarphp/opayo
```

### Configure the service

Add the opayo config to the `config/services.php` file.

```php
// ...
'opayo' => [
    'vendor' => env('OPAYO_VENDOR'),
    'env' => env('OPAYO_ENV', 'test'),
    'key' => env('OPAYO_KEY'),
    'password' => env('OPAYO_PASSWORD'),
    'host' => env('OPAYO_HOST'),
],
```


### Enable the driver

Set the driver in `config/lunar/payments.php`

```php
<?php

return [
    // ...
    'types' => [
        'card' => [
            // ...
            'driver' => 'opayo',
        ],
    ],
];
```



## Configuration

Below is a list of the available configuration options this package uses in `config/lunar/opayo.php`

| Key | Default | Description |
| --- | --- | --- |
| `policy` | `automatic` | Determines the policy for taking payments and whether you wish to capture the payment manually later or take payment straight away. Available options `deferred` or `automatic` |

---

## Backend Usage

### Get a merchant key

```php
Lunar\Opayo\Facades\Opayo::getMerchantKey();
```

### Authorize a charge

```php
$response = \Lunar\Facades\Payments::driver('opayo')->cart(
    $cart = CartSession::current()->calculate()
)->withData([
    'merchant_key' => $request->get('merchantSessionKey'),
    'card_identifier' => $request->get('cardToken'),
    'browserLanguage' => $request->get('browserLanguage'),
    'challengeWindowSize' => $request->get('challengeWindowSize'),
    'browserIP' => $request->ip(),
    'browserAcceptHeader' => $request->header('accept'),
    'browserUserAgent' => $request->get('browserUserAgent'),
    'browserJavaEnabled' => $request->get('browserJavaEnabled', false),
    'browserColorDepth' => $request->get('browserColorDepth'),
    'browserScreenHeight' => $request->get('browserScreenHeight'),
    'browserScreenWidth' => $request->get('browserScreenWidth'),
    'browserTZ' => $request->get('browserTZ'),
    'status' => 'payment-received',
])->authorize();
```

When authorizing a charge, you may be required to submit extra authentication in the form of 3DSV2, you can handle this in your payment endpoint.

```php
if (is_a($response, \Lunar\Opayo\Responses\ThreeDSecureResponse::class)) {
  return response()->json([
      'requires_auth' => true,
      'data' => $response,
  ]);
}
```

`$response` will contain all the 3DSV2 information from Opayo.

You can find more information about this using the following links:

- [3-D Secure explained](https://www.elavon.co.uk/resource-center/help-with-your-solutions/opayo/fraud-prevention/3D-Secure.html)
- [3D Secure Transactions](https://developer.elavon.com/products/opayo-direct/v1/3d-secure-transactions)
- Stack overflow [SagePay 3D Secure V2 Flow](https://stackoverflow.com/questions/65329436/sagepay-3d-secure-v2-flow)

Once you have handled the 3DSV2 response on your storefront, you can then authorize again.

```php
$response = Payments::driver('opayo')->cart(
    $cart = CartSession::current()->calculate()
)->withData([
    'cres' => $request->get('cres'),
    'pares' => $request->get('pares'),
    'transaction_id' => $request->get('transaction_id'),
])->threedsecure();

if (! $response->success) {
    abort(401);
}

```

### Opayo card tokens

When authenticated users make an order on your store, it can be good to offer the ability to save their card information for future use. Whilst we don't store the actual card details, we can use card tokens which represent the card the user has used before.

> You must have saved payments enabled on your Opayo account because you can use these.

To save a card, pass in the `saveCard` data key when authorizing a payment.

```php
$response = \Lunar\Facades\Payments::driver('opayo')->cart(
    $cart = CartSession::current()->calculate()
)->withData([
    // ...
    'saveCard' => true
])->authorize();
```

Assuming everything went well, there will be a new entry in the `opayo_tokens` table, associated to the authenticated user. You can then display these card representations at checkout for the user to select. The `token` is what replaces the `card_identifier` data key.

```php
$response = \Lunar\Facades\Payments::driver('opayo')->cart(
    $cart = CartSession::current()->calculate()
)->withData([
    // ...
    'card_identifier' => $request->get('cardToken'),
    'reusable' => true
])->authorize();
```

Responses are then handled the same as any other transaction.

## Contributing

Contributions are welcome, if you are thinking of adding a feature, please submit an issue first so we can determine whether it should be included.
