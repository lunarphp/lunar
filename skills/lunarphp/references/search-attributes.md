# Search & Attributes

## Search

Lunar search is built on Laravel Scout with pre-configured indexers per model.

### Configuration

```php
// config/lunar/search.php
'models' => [
    Lunar\Models\Product::class,
    Lunar\Models\Collection::class,
    // ...
],
'engine_map' => [
    Lunar\Models\Product::class => 'typesense',
    Lunar\Models\Order::class => 'meilisearch',
],
'indexers' => [
    Lunar\Models\Product::class => Lunar\Search\ProductIndexer::class,
    // ...
],
```

Requires `soft_delete: true` in `config/scout.php`.

### Indexing

```bash
php artisan lunar:search:index
php artisan lunar:search:index "Lunar\Models\Product" --refresh
php artisan lunar:search:index "Lunar\Models\Order" --flush
```

For Meilisearch, run setup to configure filterable/sortable attributes:

```bash
php artisan lunar:meilisearch:setup
```

### Storefront Search Add-on

For faceted search with consistent API across engines:

```bash
composer require lunarphp/search
```

```php
use Lunar\Search\Facades\Search;

$results = Search::search('boots')
    ->paginate(20)
    ->get();
```

> For a complete search walkthrough with faceted filtering, see the [Search & Product Discovery guide](https://docs.lunarphp.com/1.x/guides/search.md).

## Attributes

### Attribute System

Custom data stored as JSON (`attribute_data`) with typed field values.

### Field Types

| Type | Description |
|------|-------------|
| `Lunar\FieldTypes\Text` | Plain or rich text |
| `Lunar\FieldTypes\TranslatedText` | Translatable text (one value per locale) |
| `Lunar\FieldTypes\Number` | Integer or decimal |
| `Lunar\FieldTypes\Toggle` | Boolean |
| `Lunar\FieldTypes\Dropdown` | Single select from options |
| `Lunar\FieldTypes\ListField` | Reorderable list of text values |
| `Lunar\FieldTypes\File` | File references |
| `Lunar\FieldTypes\YouTube` | YouTube video ID/URL |
| `Lunar\FieldTypes\Vimeo` | Vimeo video ID/URL |

### Saving & Reading

```php
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;

$product->attribute_data = collect([
    'meta_title' => new Text('Best Screwdriver'),
    'description' => new TranslatedText(collect([
        'en' => new Text('Great tool'),
        'fr' => new Text('Super outil'),
    ])),
]);
$product->save();

// Read
$product->translateAttribute('name');       // Current locale
$product->translateAttribute('name', 'fr'); // Specific locale
$product->attr('name');                      // Shorthand
```

### Custom Attributable Models

```php
use Lunar\Base\Casts\AsAttributeData;
use Lunar\Base\Traits\HasAttributes;
use Lunar\Facades\AttributeManifest;

class MyModel extends Model
{
    use HasAttributes;

    protected $casts = [
        'attribute_data' => AsAttributeData::class,
    ];
}

// Register in service provider
AttributeManifest::addType(MyModel::class);
```

## References

- [Search Reference](https://docs.lunarphp.com/1.x/reference/search.md)
- [Attributes Reference](https://docs.lunarphp.com/1.x/reference/attributes.md)
- [Media Reference](https://docs.lunarphp.com/1.x/reference/media.md)
- [URLs Reference](https://docs.lunarphp.com/1.x/reference/urls.md)
