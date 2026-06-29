<?php

namespace Lunar\Core\Models\Builders;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Lunar\Core\Models\Concerns\ResolvesRegisteredScopes;

/**
 * The default Eloquent builder for every Lunar model. It owns the canonical
 * registry of consumer-registered local scopes, keyed by model class, so a
 * consumer can register an optional, named scope on a specific model from
 * outside the class (e.g. a service provider) without subclassing. Registered
 * scopes then behave exactly like a native local scope.
 *
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends EloquentBuilder<TModel>
 */
class Builder extends EloquentBuilder
{
    use ResolvesRegisteredScopes;

    /**
     * Registered local scopes, keyed by model class then scope name.
     *
     * @var array<class-string, array<string, Closure>>
     */
    protected static array $registeredScopes = [];

    /**
     * Register a local scope against a model class. Re-registering a name
     * replaces the previous closure (last-wins).
     */
    public static function registerScope(string $model, string $name, Closure $scope): void
    {
        static::$registeredScopes[$model][$name] = $scope;
    }

    /**
     * The scope registered for a model under the given name, if any.
     */
    public static function resolveScope(string $model, string $name): ?Closure
    {
        return static::$registeredScopes[$model][$name] ?? null;
    }

    /**
     * Forget every registered scope. Intended for test isolation, since the
     * registry is static and persists across a process like macros do.
     */
    public static function flushScopes(): void
    {
        static::$registeredScopes = [];
    }
}
