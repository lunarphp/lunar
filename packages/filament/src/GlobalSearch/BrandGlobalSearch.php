<?php

namespace Lunar\Filament\GlobalSearch;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Contracts\Brand as BrandContract;

/**
 * @extends GlobalSearchDescriptor<Brand>
 */
class BrandGlobalSearch extends GlobalSearchDescriptor
{
    public static function getModelContract(): string
    {
        return BrandContract::class;
    }

    public static function getSearchableAttributes(): array
    {
        return [
            'name',
        ];
    }

    public static function getResultTitle(Model $record): string|Htmlable
    {
        /** @var Brand $record */
        return (string) $record->name;
    }

    public static function getResultDetails(Model $record): array
    {
        /** @var Brand $record */
        return [
            __('lunar-filament::global-search.brands.details.products') => $record->products()->count(),
        ];
    }
}
