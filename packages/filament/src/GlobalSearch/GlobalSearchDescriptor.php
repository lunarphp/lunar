<?php

namespace Lunar\Filament\GlobalSearch;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

/**
 * Describes how a Lunar model participates in Filament global search.
 *
 * Subclasses live next to the model they describe (one per searchable Lunar
 * model) and replace per-resource `getGlobalSearch*` methods, so a consumer
 * can compose their own Filament resource and get Lunar's global-search
 * behaviour by referencing the descriptor.
 *
 * @template TModel of Model
 */
abstract class GlobalSearchDescriptor
{
    /**
     * Fully-qualified model class or contract this descriptor describes.
     * If a contract interface is returned, it is resolved to the concrete
     * model bound in the container.
     *
     * @return class-string<TModel>
     */
    abstract public static function getModelContract(): string;

    /**
     * Concrete model class for runtime use — resolves contract interfaces
     * through the container.
     *
     * @return class-string<TModel>
     */
    public static function getModel(): string
    {
        $class = new ReflectionClass(static::getModelContract());

        if ($class->isInterface()) {
            return app()->get(static::getModelContract())::class;
        }

        return static::getModelContract();
    }

    /**
     * Attributes (and dot-path relations) to search across when Scout is not
     * enabled for the model.
     *
     * @return array<int, string|array<int, string>>
     */
    abstract public static function getSearchableAttributes(): array;

    /**
     * Key/value details shown beneath each global-search result.
     *
     * @param  TModel  $record
     * @return array<string, string|Htmlable|null>
     */
    abstract public static function getResultDetails(Model $record): array;

    /**
     * Title displayed for each global-search result. Defaults to the model's
     * configured `name` attribute (translated).
     *
     * @param  TModel  $record
     */
    public static function getResultTitle(Model $record): string|Htmlable
    {
        if (method_exists($record, 'translate')) {
            $value = $record->translate('name');

            if (filled($value)) {
                return (string) $value;
            }
        }

        return (string) ($record->name ?? $record->getKey());
    }

    /**
     * Base Eloquent query (eager-loads, scopes) used as the starting point
     * for global-search constraint building. Override to add `with()` calls.
     */
    public static function getEloquentQuery(): Builder
    {
        $model = static::getModel();

        return $model::query();
    }
}
