<?php

namespace Lunar\Panel\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Language;

/**
 * Query helpers for translated columns — JSON columns holding a
 * {locale: text} map, such as `products.name`. Comparing or ordering the raw
 * column works on the JSON document (its locale keys included), so these
 * read the values through Laravel's `column->locale` JSON paths, which every
 * supported database compiles. Matching and ordering ignore case.
 */
class TranslatedColumn
{
    /**
     * Constrain the query to rows whose value in any store language (or the
     * current locale) contains the term.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function whereLike(Builder $query, string $column, string $term, string $boolean = 'and'): Builder
    {
        $locales = Language::query()->pluck('code')->prepend(app()->getLocale())->unique();
        $like = '%'.mb_strtolower($term).'%';

        return $query->where(function (Builder $query) use ($column, $locales, $like) {
            foreach ($locales as $locale) {
                $query->orWhereRaw('lower('.static::path($query, $column, $locale).') like ?', [$like]);
            }
        }, boolean: $boolean);
    }

    /**
     * Order the query by the value in the current locale, falling back to the
     * default language for rows without (or with an empty) translation.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function orderBy(Builder $query, string $column, string $direction = 'asc'): Builder
    {
        $locales = collect([app()->getLocale(), Language::getDefault()?->code])->filter()->unique();

        $values = $locales->map(fn (string $locale) => 'nullif('.static::path($query, $column, $locale).", '')");

        // SQLite's coalesce() needs at least two arguments.
        $value = $values->count() > 1 ? 'coalesce('.$values->implode(', ').')' : $values->first();

        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $query->orderByRaw("lower({$value}) {$direction}");
    }

    /**
     * The compiled JSON path selecting one locale's value from the column.
     *
     * @param  Builder<covariant Model>  $query
     */
    protected static function path(Builder $query, string $column, string $locale): string
    {
        return $query->getQuery()->getGrammar()->wrap($query->qualifyColumn($column).'->'.$locale);
    }
}
