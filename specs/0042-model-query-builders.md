# 0042 — Model query builders (registerable scopes)

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-06-29
- TODO item: Per-model query scopes without subclassing

## Problem

Retiring model class substitution (see [[0041-retire-model-class-substitution]]) leaves one query-extension gap. A consumer can, from a service provider, add to a Lunar model:

- a relationship — `Model::resolveRelationUsing(...)`,
- a global (always-on) scope — `Model::addGlobalScope(...)`,
- a method — a model macro (`HasMacros`).

But there is **no native way to register an optional, local (named) scope on a specific model** from outside the class. Local scopes resolve only through `scopeX()` / `#[Scope]` methods declared on the model; Laravel offers `resolveRelationUsing` for relations and `addGlobalScope` for global scopes, but nothing for local scopes. The nearest natives are imperfect: `Builder::macro()` registers on **every** Eloquent builder (global, not per-model), and an opt-out global scope is always-on rather than optional. Subclassing — which used to cover this — is exactly what 0041 removed.

So a consumer who wants `Product::query()->featured()` — a reusable, optional filter scoped to `Product` — has no clean, native, per-model seam.

## Proposal

Give every Lunar model a single shared query builder, `Lunar\Core\Models\Builders\Builder`, returned by `Models\Base::newEloquentBuilder()`. The builder hosts a registry of scopes **keyed by model class**, and models expose `addLocalScope()` to register one. Registered scopes are then callable exactly like a native local scope.

### Registering — mirrors `resolveRelationUsing`

```php
use Lunar\Core\Models\Builders\Builder;
use Lunar\Core\Models\Product;

// in a service provider boot()
Product::addLocalScope('featured', function (Builder $query) {
    return $query->where('is_featured', true);
});

Product::addLocalScope('priorityOver', function (Builder $query, int $min) {
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
public static function addLocalScope(string $name, Closure $scope): void
{
    Builder::registerScope(static::class, $name, $scope);
}
```

`Lunar\Core\Models\Builders\Builder` extends `Illuminate\Database\Eloquent\Builder` and owns the canonical registry (keyed by model class). The `__call` resolution lives in a small `Concerns\ResolvesRegisteredScopes` trait so it can be shared with model-specific builders (see below). It resolves a registered scope only when no native scope, macro, or query method claims the name first — native always wins:

```php
/** @var array<class-string, array<string, \Closure>> */
protected static array $registeredScopes = [];

public static function registerScope(string $model, string $name, Closure $scope): void
{
    static::$registeredScopes[$model][$name] = $scope;
}

// ResolvesRegisteredScopes::__call
public function __call($method, $parameters)
{
    $scope = Builder::resolveScope($this->getModel()::class, $method);

    if ($scope !== null
        && ! $this->hasNamedScope($method)
        && ! $this->hasMacro($method)
        && ! static::hasGlobalMacro($method)) {
        return $scope($this, ...$parameters) ?? $this;
    }

    return parent::__call($method, $parameters);
}
```

Keying on `$this->getModel()::class` gives per-model isolation from one builder class — no per-model subclasses and no `protected static $macros` redeclaration footgun. `flushScopes()` clears the registry for test isolation.

### Lunar's own scopes stay on the models

Lunar's first-class scopes remain `scopeX()` / `#[Scope]` methods on the models (where they are today and are already model-isolated and type-checked). `Builder`'s only new responsibility is the consumer registry, so the shared builder never accumulates one model's typed methods that would then be callable on another.

### Models with a custom builder

`Collection` returns kalnoy's nested-set `QueryBuilder` from `newEloquentBuilder()`, which cannot also extend `Builder` (single inheritance). Rather than let it opt out — a silent no-op when a consumer registers a scope on `Collection` — the resolution logic is factored into the `ResolvesRegisteredScopes` trait and the canonical registry lives on `Builder` as static state. `Collection` returns `Builders\CollectionQueryBuilder` (extends the nested-set `QueryBuilder`, uses the trait), so `Collection::addLocalScope()` works identically. Any model that needs its own builder follows the same pattern: extend the builder it needs, use the trait.

## Alternatives considered

- **A dedicated builder class per model** (`ProductBuilder`, …), consumers extend via `ProductBuilder::macro(...)`. The "correct" OOP shape and it isolates per model, but: ~50 new public builder classes, each must redeclare `protected static $macros` or silently leak macros globally, consumers must discover the builder class name, and the only advantage over this proposal — consumers defining *typed* builder methods — does not materialise through an external seam (they register closures either way). Rejected as cost without payoff.
- **Global `Builder::macro()`**. One call, but registers on every model's builder; not per-model. Rejected.
- **Opt-out global scope** (`addGlobalScope` + `withoutGlobalScope`). Always-on with per-query bypass — fits "default filter occasionally dropped", not "optional scope occasionally applied". Rejected as a general answer; still the right tool for default filters.
- **Do nothing.** Consumers fall back to inline `where()` or a repository/query-object in their own layer. Acceptable for many, but leaves the named-per-model-scope case unserved.

## Migration impact

- **Database migrations:** none.
- **Breaking changes:** none — purely additive. `Lunar\Core\Models\Builders\Builder` and `Model::addLocalScope()` become new public contract surface (changing them later needs a spec). `Base::newEloquentBuilder()` now returns `Builders\Builder`; its declared return type is widened to `Illuminate\Database\Eloquent\Builder` so a model with a custom builder (e.g. `Collection`) can override it covariantly.
- **Upgrade path:** none required; consumers opt in.
- **Translation / locale impact:** none.
- **Filament / admin impact:** none directly; resources can use registered scopes if present.

## Resolved questions

- **API name:** `addLocalScope()`. It mirrors Laravel's `addGlobalScope` and uses Laravel's own local/global taxonomy, and its closure is a builder mutator — the same shape as `addGlobalScope`. `resolveScopeUsing` was rejected (it borrows the relation-seam name, but a relation closure is a *factory* returning an object whereas a scope closure *mutates the builder*, so the parallel is false); `addScope` was rejected as ambiguous between global and local.
- **Collision policy:** native wins. A registered scope resolves only when the name is not already a native scope (`scopeX` / `#[Scope]`), a local macro, or a global builder macro — so a consumer can never accidentally shadow one of Lunar's own scopes. Among registrations, last-wins (re-registering a name replaces the closure). Verified by the `a native scope wins over a registered scope of the same name` test.
- **Static state / test isolation:** the registry is static, so registrations persist across a process like macros do. `Builder::flushScopes()` clears it; the scope tests call it in `afterEach`.
- **Collection's nested-set builder:** composes (does not opt out). The resolution logic lives in `Concerns\ResolvesRegisteredScopes` and the registry on `Builder`; `Collection` returns `Builders\CollectionQueryBuilder` which uses the trait. Verified by the `a registered scope works on a model with a custom builder` test.

## Open questions

- Static-analysis ergonomics: registered scopes are opaque to PHPStan/IDE (true of any dynamic scope). Document the `@method` annotation a consumer can add to their own model docblock for type-safety. **Deferred to slice 3 (with the rest of v2 docs).**

## References

- Builds on [[0041-retire-model-class-substitution]] — this seam fills the one query-extension gap that removing substitution left. The 0041 capabilities review notes the gap and points here.
- Laravel 13 Eloquent docs: query scopes, `newEloquentBuilder`.

## Implementation plan

- [x] Slice 1 — `Lunar\Core\Models\Builders\Builder` + `Base::newEloquentBuilder()`, the keyed registry, `Base::addLocalScope()`, the `ResolvesRegisteredScopes` trait, and `Builders\CollectionQueryBuilder`. Collision policy (native wins) and Collection composition resolved.
- [x] Slice 2 — Tests: a registered scope is callable (chained + static + with args) on its model only, throws on others, composes with native scopes, a native scope wins on collision, and a custom-builder model (Collection) composes (`RegisteredScopesTest`). Recipe added to `ModelExtensionRecipesTest`.
- [ ] Slice 3 — Document the `@method` annotation pattern for consumer type-safety (with the rest of v2 docs).
