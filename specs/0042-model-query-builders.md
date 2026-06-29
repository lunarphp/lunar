# 0042 — Model query builders (registerable scopes)

- Status: draft
- Author: Glenn Jacobs
- Created: 2026-06-29
- TODO item: Per-model query scopes without subclassing

## Problem

Retiring model class substitution (see [[0041-retire-model-class-substitution]]) leaves one query-extension gap. A consumer can, from a service provider, add to a Lunar model:

- a relationship — `Model::resolveRelationUsing(...)`,
- a global (always-on) scope — `Model::addGlobalScope(...)`,
- a method — a model macro (`HasMacros`).

But there is **no native way to register an optional, local (named) scope on a specific model** from outside the class. Local scopes resolve only through `scopeX()` / `#[Scope]` methods declared on the model, and there is no `resolveScopeUsing` equivalent. The nearest natives are imperfect: `Builder::macro()` registers on **every** Eloquent builder (global, not per-model), and an opt-out global scope is always-on rather than optional. Subclassing — which used to cover this — is exactly what 0041 removed.

So a consumer who wants `Product::query()->featured()` — a reusable, optional filter scoped to `Product` — has no clean, native, per-model seam.

## Proposal

Give every Lunar model a single shared query builder, `Lunar\Core\Models\Builder`, returned by `Models\Base::newEloquentBuilder()`. The builder hosts a registry of scopes **keyed by model class**, and models expose `resolveScopeUsing()` to register one. Registered scopes are then callable exactly like a native local scope.

### Registering — mirrors `resolveRelationUsing`

```php
use Lunar\Core\Models\Builder;
use Lunar\Core\Models\Product;

// in a service provider boot()
Product::resolveScopeUsing('featured', function (Builder $query) {
    return $query->where('is_featured', true);
});

Product::resolveScopeUsing('priorityOver', function (Builder $query, int $min) {
    return $query->where('priority', '>', $min);
});
```

### Calling — indistinguishable from a built-in scope

```php
Product::query()->featured()->get();
Product::featured()->paginate();           // static entry, like a native scope
Product::priorityOver(5)->get();

Product::query()
    ->featured()
    ->channel($channel)
    ->orderBy('name')
    ->get();
```

A scope registered for `Product` is callable only on `Product` queries; `Order::query()->featured()` throws `BadMethodCallException`, the same as a mistyped native scope.

### Shape

`Models\Base` gains a static entry point that records the scope against the calling model class:

```php
public static function resolveScopeUsing(string $name, Closure $scope): void
{
    Builder::registerScope(static::class, $name, $scope);
}
```

`Lunar\Core\Models\Builder` extends `Illuminate\Database\Eloquent\Builder`, holds the registry, and resolves registered scopes in `__call` before falling through:

```php
/** @var array<class-string, array<string, \Closure>> */
protected static array $scopes = [];

public static function registerScope(string $model, string $name, Closure $scope): void
{
    static::$scopes[$model][$name] = $scope;
}

public function __call($method, $parameters)
{
    $model = $this->getModel()::class;

    if (isset(static::$scopes[$model][$method])) {
        return static::$scopes[$model][$method]($this, ...$parameters) ?? $this;
    }

    return parent::__call($method, $parameters);
}
```

Keying on `$this->getModel()::class` gives per-model isolation from one builder class — no per-model subclasses and no `protected static $macros` redeclaration footgun.

### Lunar's own scopes stay on the models

Lunar's first-class scopes remain `scopeX()` / `#[Scope]` methods on the models (where they are today and are already model-isolated and type-checked). `Builder`'s only new responsibility is the consumer registry, so the shared builder never accumulates one model's typed methods that would then be callable on another.

### Models with a custom builder

`Collection` returns kalnoy's nested-set `QueryBuilder` from `newEloquentBuilder()`. Either its builder extends `Lunar\Core\Models\Builder` (if compatible with the nested-set builder) or it opts out of the registry. Resolved in the implementation spike.

## Alternatives considered

- **A dedicated builder class per model** (`ProductBuilder`, …), consumers extend via `ProductBuilder::macro(...)`. The "correct" OOP shape and it isolates per model, but: ~50 new public builder classes, each must redeclare `protected static $macros` or silently leak macros globally, consumers must discover the builder class name, and the only advantage over this proposal — consumers defining *typed* builder methods — does not materialise through an external seam (they register closures either way). Rejected as cost without payoff.
- **Global `Builder::macro()`**. One call, but registers on every model's builder; not per-model. Rejected.
- **Opt-out global scope** (`addGlobalScope` + `withoutGlobalScope`). Always-on with per-query bypass — fits "default filter occasionally dropped", not "optional scope occasionally applied". Rejected as a general answer; still the right tool for default filters.
- **Do nothing.** Consumers fall back to inline `where()` or a repository/query-object in their own layer. Acceptable for many, but leaves the named-per-model-scope case unserved.

## Migration impact

- **Database migrations:** none.
- **Breaking changes:** none — purely additive. `Lunar\Core\Models\Builder` and `Model::resolveScopeUsing()` become new public contract surface (changing them later needs a spec).
- **Upgrade path:** none required; consumers opt in.
- **Translation / locale impact:** none.
- **Filament / admin impact:** none directly; resources can use registered scopes if present.

## Open questions

- API name: `resolveScopeUsing()` (mirrors `resolveRelationUsing`) vs `registerScope()` vs `scope()`. Recommendation: `resolveScopeUsing()` for symmetry. **Owner: resolve before `accepted`.**
- Collision policy when a registered scope name shadows a real method, relation, or native scope on the model — throw, warn, or last-wins? **Owner: implementation spike.**
- Static state: the registry is a static, so registrations persist across a process like macros do. Confirm test isolation and whether a reset hook is warranted.
- Does `Collection`'s nested-set builder compose with `Lunar\Core\Models\Builder`, or does it opt out? **Owner: implementation spike.**
- Static-analysis ergonomics: registered scopes are opaque to PHPStan/IDE (true of any dynamic scope). Document the `@method` annotation a consumer can add to their own model docblock for type-safety.

## References

- Builds on [[0041-retire-model-class-substitution]] — this seam fills the one query-extension gap that removing substitution left. The 0041 capabilities review notes the gap and points here.
- Laravel 13 Eloquent docs: query scopes, `newEloquentBuilder`.

## Implementation plan

- [ ] Slice 1 — Spike: `Lunar\Core\Models\Builder` + `Base::newEloquentBuilder()`, the keyed registry, and `Base::resolveScopeUsing()`. Resolve the `Collection`/nested-set builder question and the collision policy.
- [ ] Slice 2 — Tests: a registered scope is callable (chained + static + with args) on its model only, throws on others, and composes with native scopes and global scopes. Add a recipe to `ModelExtensionRecipesTest`.
- [ ] Slice 3 — Document the `@method` annotation pattern for consumer type-safety (with the rest of v2 docs).
