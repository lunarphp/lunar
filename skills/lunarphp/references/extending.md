# Extending Lunar

## Dynamic Relationships (Recommended)

```php
use Lunar\Models\Order;
use App\Models\Ticket;

Order::resolveRelationUsing('ticket', function ($orderModel) {
    return $orderModel->belongsTo(Ticket::class, 'ticket_id');
});
```

Register in a service provider's `boot` method.

## Model Replacement

For deeper customization (overriding methods, adding scopes):

```php
use Lunar\Facades\ModelManifest;

// Single model
ModelManifest::replace(
    Lunar\Models\Contracts\Product::class,
    App\Model\Product::class,
);

// Scan directory
ModelManifest::addDirectory(__DIR__.'/../Models');
```

Custom model must extend the Lunar model. Contract-based type hints work with route binding and relationships.

## Extending Cart Calculations

Define custom pipelines in `config/lunar/cart.php`:

```php
'pipelines' => [
    App\Pipelines\Cart\CustomCartPipeline::class,
],
```

Override order creation action:

```php
'actions' => [
    'order_create' => App\Actions\Carts\CustomCreateOrder::class,
],
```

Add custom validators:

```php
'validators' => [
    'order_create' => [App\Validation\Cart\CustomValidator::class],
],
```

## Extending Other Systems

- [Extending Carts](https://docs.lunarphp.com/1.x/extending/carts.md)
- [Extending Discounts](https://docs.lunarphp.com/1.x/extending/discounts.md)
- [Extending Models](https://docs.lunarphp.com/1.x/extending/models.md)
- [Extending Orders](https://docs.lunarphp.com/1.x/extending/orders.md)
- [Extending Payments](https://docs.lunarphp.com/1.x/extending/payments.md)
- [Extending Search](https://docs.lunarphp.com/1.x/extending/search.md)
- [Extending Shipping](https://docs.lunarphp.com/1.x/extending/shipping.md)
- [Extending Taxation](https://docs.lunarphp.com/1.x/extending/taxation.md)

## Admin Panel Extension

- [Extending the Admin Panel (Overview)](https://docs.lunarphp.com/1.x/admin/extending/overview.md)
- [Extending Pages](https://docs.lunarphp.com/1.x/admin/extending/pages.md)
- [Extending Resources](https://docs.lunarphp.com/1.x/admin/extending/resources.md)
- [Extending Relation Managers](https://docs.lunarphp.com/1.x/admin/extending/relation-managers.md)
- [Extending Order Management](https://docs.lunarphp.com/1.x/admin/extending/order-management.md)
- [Extending Attributes](https://docs.lunarphp.com/1.x/admin/extending/attributes.md)
- [Access Control](https://docs.lunarphp.com/1.x/admin/extending/access-control.md)
- [Configuration](https://docs.lunarphp.com/1.x/admin/extending/configuration.md)
- [Extending the Panel](https://docs.lunarphp.com/1.x/admin/extending/panel.md)
- [Developing Addons](https://docs.lunarphp.com/1.x/admin/extending/addons.md)
