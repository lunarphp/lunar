
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


