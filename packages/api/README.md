# Lunar API

Storefront and admin HTTP APIs for Lunar, built on one kernel: a resource model, a JSON:API-style query grammar (`include`, `fields[type]`, `filter[name][op]`, `sort`, `page[number|size]`), a `data` / `meta` / `links` envelope and JSON:API error objects. The design is spec 0077 in the monorepo's `specs/` directory.

## Surfaces

| Surface | Prefix | Principal |
| --- | --- | --- |
| Storefront | `/api/storefront/v1` | guests, and customers through the host guard named by `lunar.api.storefront.guard` |
| Admin | `/api/admin/v1` | API keys (`lunar:api:key create`) or any guard whose user answers `can()` |

Every model is addressed by its `public_id`. `GET /{surface}/v1/_schema` (or `php artisan lunar:api:schema {surface}`) lists every resource with its fields, includes, filters, sorts and routes as the caller may see them.

Storefront requests carry their context in headers: `X-Lunar-Channel`, `X-Lunar-Currency`, `Accept-Language`, and the signed `X-Lunar-Cart` token returned when a cart is created.

## Extending a resource

```php
use Lunar\Api\Facades\Api;
use Lunar\Api\Resources\{Embed, Field, Filter, ResourceExtension, Sort};
use Lunar\Api\Storefront\Resources\V1\ProductResource;

class ReviewsProductExtension extends ResourceExtension
{
    public function extends(): string { return ProductResource::class; }

    public function fields(): array
    {
        return [
            Field::make('average_rating', fn (Product $product) => $product->reviews_avg_rating ?? 0)
                ->eagerLoad(withAvg: ['reviews' => 'rating']),
        ];
    }

    public function includes(): array
    {
        return [Embed::relation('reviews', ReviewResource::class)];
    }

    public function filters(): array
    {
        return [Filter::column('min_rating', 'reviews_avg_rating')->operators(['gte'])];
    }
}

// In a service provider's boot():
Api::storefront('v1')->resource(ReviewResource::class);
Api::storefront('v1')->extend(ProductResource::class, ReviewsProductExtension::class);
```

A host that needs to change a built-in resource's own fields replaces it with a subclass: `Api::storefront('v1')->replace(ProductResource::class, MyProductResource::class)`. Extensions are keyed by the built-in class, so they keep applying.
