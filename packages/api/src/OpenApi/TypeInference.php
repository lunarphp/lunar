<?php

namespace Lunar\Api\OpenApi;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Casts\AsEncryptedArrayObject;
use Illuminate\Database\Eloquent\Casts\AsEncryptedCollection;
use Illuminate\Database\Eloquent\Casts\AsEnumArrayObject;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Lunar\Api\Resources\Embed;
use Lunar\Api\Resources\Field;
use Lunar\Api\Resources\Filter;
use Lunar\Api\Resources\Translations;
use Throwable;

/**
 * Works out the wire type of fields, filters and includes that did not
 * declare one, from a fresh model instance: casts and date attributes for
 * values, the relation class for include cardinality. Never queries.
 */
final class TypeInference
{
    /**
     * @param  class-string<Model>  $model
     */
    public static function field(Field $field, string $model, Translations $translations): Schema
    {
        $schema = $field->declaredType();

        if ($schema === null && $field->isTranslatable()) {
            $schema = $translations === Translations::Resolved ? Schema::string() : Schema::translations();
        }

        if ($schema === null && ($attribute = $field->attribute()) !== null) {
            $schema = self::attribute($model, $attribute);
        }

        $schema ??= Schema::untyped();

        return $field->isNullable() ? $schema->nullable() : $schema;
    }

    /**
     * @param  class-string<Model>  $model
     */
    public static function filter(Filter $filter, string $model): Schema
    {
        if ($declared = $filter->declaredType()) {
            return $declared;
        }

        if ($filter->isScope()) {
            return Schema::boolean();
        }

        if (($column = $filter->comparedColumn()) !== null) {
            return self::attribute($model, $column);
        }

        return Schema::untyped();
    }

    /**
     * Whether an include resolves to a list of resources.
     *
     * @param  class-string<Model>  $model
     */
    public static function many(Embed $embed, string $model): bool
    {
        if ($embed->declaredMany() !== null) {
            return $embed->declaredMany();
        }

        $relation = $embed->eloquentRelation();

        if ($relation === null || ! method_exists($model, $relation)) {
            return false;
        }

        try {
            $instance = (new $model)->{$relation}();
        } catch (Throwable) {
            return false;
        }

        return $instance instanceof Relation && (
            $instance instanceof HasMany
            || $instance instanceof BelongsToMany
            || $instance instanceof MorphMany
            || $instance instanceof MorphToMany
            || $instance instanceof HasManyThrough
        );
    }

    /**
     * The type of a model attribute from its cast. Uncast attributes are strings.
     *
     * @param  class-string<Model>  $model
     */
    public static function attribute(string $model, string $attribute): Schema
    {
        $instance = new $model;

        if (in_array($attribute, $instance->getDates(), true)) {
            return Schema::dateTime();
        }

        $cast = $instance->getCasts()[$attribute] ?? null;

        if ($cast === null) {
            return Schema::string();
        }

        if (enum_exists($cast)) {
            return Schema::enum($cast);
        }

        $name = strtolower($cast);
        $base = explode(':', $name, 2)[0];

        return match (true) {
            in_array($base, ['int', 'integer', 'timestamp'], true) => Schema::integer(),
            in_array($base, ['real', 'float', 'double', 'decimal'], true) => Schema::number(),
            in_array($base, ['bool', 'boolean'], true) => Schema::boolean(),
            in_array($base, ['date', 'immutable_date'], true) => Schema::date(),
            in_array($base, ['datetime', 'immutable_datetime', 'custom_datetime', 'immutable_custom_datetime'], true) => Schema::dateTime(),
            in_array($base, ['array', 'json', 'object', 'collection'], true) => Schema::any(),
            str_starts_with($name, 'encrypted:') => Schema::any(),
            self::isCollectionCast($cast) => Schema::any(),
            default => Schema::string(),
        };
    }

    private static function isCollectionCast(string $cast): bool
    {
        $class = explode(':', $cast, 2)[0];

        return in_array($class, [
            AsArrayObject::class,
            AsCollection::class,
            AsEncryptedArrayObject::class,
            AsEncryptedCollection::class,
            AsEnumArrayObject::class,
            AsEnumCollection::class,
        ], true);
    }
}
