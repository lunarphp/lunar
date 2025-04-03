
## Lunar Search

This packages brings E-Commerce search to Lunar.
---

## Requirements
- Lunar >= 1.x

## License

Lunar is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Installation

### Require the composer package

```sh
composer require lunarphp/search
```

## Usage

### Configuration

Most configuration is done via `config/lunar/search.php`. Here you can specify which facets should be used and how they are displayed.

```php
'facets' => [
        \Lunar\Models\Product::class => [
            'brand' => [
                'label' => 'Brand',
            ],
            'colour' => [
                'label' => 'Colour',
            ],
            'size' => [
                'label' => 'Size',
            ],
            'shoe-size' => [
                'label' => 'Shoe Size',
            ]
        ]
],
```

### Basic Search

At a basic level, you can search models using the provided facade.

```php
use Lunar\Search\Facades\Search;

// Search on a specific model
$results = Search::on(\Lunar\Models\Collection::class)->query('Hoodies')->get();

// Search on Lunar\Models\Product by default.
$results = Search::query('Hoodies')->get();
```

Under the hood this will detect what Scout driver is mapped under `lunar.search.engine_map` and 
then perform a search using that given driver. To increase performance the results will not be 
hydrated from the database, but instead will be the raw results from the search provider.


### Handling the response

Searching returns a `Lunar\Data\SearchResult` DTO which you can use in your templates:

```php
use Lunar\Search\Facades\Search;
$results = Search::query('Hoodies')->get();
```

```bladehtml
<!-- Loop through results-->
@foreach($results->hits as $hit)
    {{ $hit->document['name'] }}
@endforeach

<!-- Loop through facets -->
@foreach($results->facets as $facet)
<span>
    <strong>{{ $facet->label }}</strong>
    @foreach($facet->values as $facetValue)
        <input type="checkbox" value="{{ $facetValue->value }}" />
        <span
            @class([
                'text-blue-500' => $facetValue->active,
            ])
        >
            {{ $facetValue->label }}
        </span>
        {{ $facetValue->count }}
    @endforeach
</div>
@endforeach
```

