# Lunar API

Storefront and admin HTTP APIs for Lunar, built on one kernel: a resource model, a JSON:API-style query grammar (`include`, `fields[type]`, `filter[name][op]`, `sort`, `page[number|size]`), a `data` / `meta` / `links` envelope and JSON:API error objects. The design is spec 0077 in the monorepo's `specs/` directory.

## Surfaces

| Surface | Prefix | Principal |
| --- | --- | --- |
| Storefront | `/api/storefront/v1` | guests, and customers through the host guard named by `lunar.api.storefront.guard` |
| Admin | `/api/admin/v1` | API keys (`lunar:api:key create`) or any guard whose user answers `can()` |

Every model is addressed by its `public_id`.

## OpenAPI

Each surface serves its OpenAPI 3.1 document at `GET /{prefix}/v1/openapi.json`, and `php artisan lunar:api:openapi {surface} [--api-version=v1] [--out=path] [--yaml]` writes the same document from the console. The document is generated from the resource registry, so add-on fields, filters, includes and routes appear in it without any extra step, and it always describes the whole surface: fields gated by an ability carry `x-lunar-requires` rather than being omitted. Point Orval, openapi-typescript or Kiota at it for a typed client, or list it in a Mintlify `docs.json` for reference pages; operations are tagged by resource type with the resource label as the group name.

Types come from `Field::make(...)->type(Schema::...)` and `Filter::...->type(...)`; fields that read a model attribute infer their type from the model's casts, translatable fields serialise as strings or `TranslationMap`s per surface, and includes reference the related resource's schema. Descriptions come from `->describe()` on fields, filters, includes and sorts, `Resource::label()` / `Resource::description()`, and the `#[Operation]` / `#[Responds]` attributes on controller methods; `ResourceController::index()` and `show()` are described by convention. Request bodies are derived from the form request's `rules()` (refined by an optional `descriptions()` method) or declared outright by implementing `DescribesSchema`. Anything the declarative model does not cover goes through `Api::storefront('v1')->tapDocument(fn (Document $document) => ...)`.

Storefront requests carry their context in headers: `X-Lunar-Channel`, `X-Lunar-Currency`, `Accept-Language`, and the signed `X-Lunar-Cart` token returned when a cart is created.

## Extending a resource

```php
use Lunar\Api\Facades\Api;
use Lunar\Api\OpenApi\Schema;
use Lunar\Api\Resources\{Embed, Field, Filter, ResourceExtension, Sort};
use Lunar\Api\Storefront\Resources\V1\ProductResource;

class ReviewsProductExtension extends ResourceExtension
{
    public function extends(): string { return ProductResource::class; }

    public function fields(): array
    {
        return [
            Field::make('average_rating', fn (Product $product) => $product->reviews_avg_rating ?? 0)
                ->eagerLoad(withAvg: ['reviews' => 'rating'])
                ->type(Schema::number())
                ->describe('Mean review rating, 0 when unreviewed.'),
        ];
    }

    public function includes(): array
    {
        return [Embed::relation('reviews', ReviewResource::class)];
    }

    public function filters(): array
    {
        return [Filter::column('min_rating', 'reviews_avg_rating')->operators(['gte'])->type(Schema::number())];
    }
}

// In a service provider's boot():
Api::storefront('v1')->resource(ReviewResource::class);
Api::storefront('v1')->extend(ProductResource::class, ReviewsProductExtension::class);
```

A host that needs to change a built-in resource's own fields replaces it with a subclass: `Api::storefront('v1')->replace(ProductResource::class, MyProductResource::class)`. Extensions are keyed by the built-in class, so they keep applying.
