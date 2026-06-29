# 0041 — Retire model class substitution

- Status: draft
- Author: Glenn Jacobs
- Created: 2026-06-29
- TODO item: Reconsider the model-extending system

## Problem

Lunar lets a consumer replace a core model class with their own subclass: register `App\Models\Product extends Lunar\Core\Models\Product` and Lunar hands back instances of the consumer's class everywhere — queries, relations, the morph map, factories. This is implemented by `Models\Concerns\HasModelExtending` plus the `ModelManifest` registry, and is exercised in CI by the `LUNAR_TESTING_REPLACE_MODELS` matrix leg and the `tests/core/Stubs/Models` subclasses.

The mechanism keeps **two live concrete class identities for one logical model** — the canonical `Lunar\Core\Models\X` and the consumer's subclass — and Eloquent keys almost everything (events, observers, global scopes, the morph map, factory resolution) on concrete class name. Holding two names for one model turns every one of those into a latent bug site:

- `HasModelExtending::fireModelEvent` re-dispatches every model event under the canonical class so listeners bound there fire for the subclass. Because self-registering traits (e.g. kalnoy `NodeTrait`) boot on **both** classes, their listeners are registered twice and run twice. For non-idempotent listeners this corrupts state — the nested-set delete sweep ran twice and deleted a re-parented `Collection` child. The fix had to reflect into the dispatcher's wrapper closures to dedupe listeners by source signature: framework-fighting machinery guarding a self-inflicted problem.
- `newModelQuery` clones the model into the concrete class via `replicateInto` inside `withoutEvents` on every query.
- `getTable`, `getForeignKey`, `getMorphClass` and `__callStatic` all walk the parent chain or forward magically.

The bug above was not bad luck; it is the structural consequence of the design, and it surfaced only on the post-merge extending matrix — silent until one specific trait/operation combination tripped it. There will be more of the same shape (global scopes, morph lookups, casts), each silent until hit.

The event double-fire is not the only native that substitution breaks. Verified while writing the replacement-recipe tests: an externally-registered global scope (`Product::addGlobalScope(...)`) is silently dropped under extending. The scope registers on the canonical class, but `newModelQuery` runs queries as the replaced class, whose global-scope registry is separate — so `Product::pluck('id')` returns every row, ignoring the scope. A native Laravel extension point simply does not survive the class-identity split.

A point-fix for the event bug (deduping the re-dispatched listeners) was prototyped and deliberately discarded: it is itself framework-fighting code that slice 2 deletes wholesale, so carrying it would mean shipping a guard we never keep. Consequently the post-merge `extend-models` CI leg is **currently red on `2.x`** and stays red until this spec's removal lands. That failing leg is the live signal that this work is outstanding; it is not muted in the interim because doing so is throwaway churn against the same `tests.yml` slice 2 rewrites.

The deeper issue: the capability this machinery exists to provide is the weakest-value one on offer. The project already preaches "don't make consumers subclass core models for framework plumbing" and "the container is for substitutions, config is for values"; behaviour overrides belong on the action seams (spec 0029). Class substitution is the one place the codebase contradicts its own architecture, carried over from v1.

## Proposal

Remove consumer model **class substitution** in v2. Models become the package's own records; consumers extend them through native Laravel mechanisms and the action-swap seam, with one new, single-purpose escape hatch for the only need natives do not cover (casts/accessors on consumer-added columns).

### Capabilities review

The only question that decides whether a capability needs substitution: can a consumer do it to a class they do not own, from a service provider, **without subclassing**? Reviewed against the Laravel 13 Eloquent docs (getting-started, mutators & casting, serialization).

**Already covered natively — no subclassing, no Lunar machinery:**

| Need | Native external mechanism |
| --- | --- |
| Add a relationship | `Model::resolveRelationUsing('x', fn …)` |
| Add a method | model / query-builder macros (existing `HasMacros`) |
| React to lifecycle | `Model::observe(Obs::class)` or `Event::listen('eloquent.*: Class')` in a provider |
| Add a global scope | `Model::addGlobalScope(new Scope)` — a public static method, callable from a provider |
| Query sugar (pseudo local-scope) | `Builder::macro(...)` |
| Default eager loads (`$with`) | a global scope that calls `->with(...)` |
| Search indexing | existing `Searchable` concern |
| Serialization tweaks | `append()` / `makeVisible()` per instance, or API Resources in the consumer's layer |

**Genuinely class-only — the gaps:**

| Need | Why it cannot be done externally | Legitimate? |
| --- | --- | --- |
| Casts (`casts()` / custom cast classes) | `casts()` is a class method; `mergeCasts()` is per-instance and documented as temporary — no global pre-hydration hook | **Yes — common** |
| Accessors / mutators (`Attribute` methods) | must be a method on the class; macros are `->foo()` calls, not `$model->foo` attribute resolution | Yes (same family — a custom cast class is a get/set pair, so the cast seam covers it) |
| `$appends` / `$hidden` / `$visible` globally | class-level PHP attributes or per-instance only | Marginal — API Resources cover the real need |
| `$table`, `$connection`, `$primaryKey`, `$keyType`, `$timestamps`, `$dateFormat` | protected class properties; no global setter | Niche — connection/prefix are normally global config |
| Soft deletes / Prunable / `newEloquentBuilder` / `newCollection` | structural traits/overrides | Lunar-owned; not a consumer concern |

There is exactly **one** legitimate, common need with no clean native external path: **casts and accessors for columns the consumer adds.** A consumer adds a `metadata` JSON column or an enum `status` column by migration and wants it cast consistently everywhere Lunar hands them the model (admin, serialization, their own code). Today, without subclassing, the only routes are `mergeCasts()` on every instance or a `retrieved`-event hack. Everything else is already native, consumer-layer, or structural and Lunar-owned.

### The replacement: an external cast registry

Provide a sanctioned seam to register casts (including custom cast classes, which double as accessor/mutator pairs) for added attributes, from a consumer's service provider:

```php
use Lunar\Core\Models\Product;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

Product::extendCasts([
    'external_ref' => 'string',
    'metadata'     => AsArrayObject::class,
    'status'       => FulfilmentStatus::class,
]);
```

Backed by a concern on `Models\Base` holding a static per-class registry and merging it into the resolved casts:

```php
trait HasExtendableCasts
{
    /** @var array<class-string, array<string, string>> */
    protected static array $extendedCasts = [];

    public static function extendCasts(array $casts): void
    {
        static::$extendedCasts[static::class] = array_merge(
            static::$extendedCasts[static::class] ?? [],
            $casts,
        );
    }

    protected function casts(): array
    {
        return static::$extendedCasts[static::class] ?? [];
    }
}
```

Eloquent merges the `casts()` method result with each model's existing `$casts` property, so per-model cast declarations are untouched. This is a tiny, single-purpose registry with none of the two-class-identity fallout. Custom cast classes cover the accessor/mutator case (a value-object cast is a get/set pair); a dedicated accessor-injection seam is deliberately out of scope (see Open questions).

### Replacement recipes

These are the supported ways to do, without substitution, everything substitution was used for. They double as the source for slice 4's migration guide and the deferred v2 docs, and as the evidence that the capabilities review holds. All run from a consumer's service-provider `boot()` unless noted. Each is **native Laravel and works today** except `extendCasts`, which this spec introduces.

**Add a relationship** — native (`resolveRelationUsing`):

```php
Product::resolveRelationUsing('supplier', fn (Product $product) => $product->belongsTo(Supplier::class));
// then: $product->supplier
```

**Add a method / computed helper** — native (model macros, via the `HasMacros` concern Lunar already applies). Use a real closure, not an arrow function: `Macroable` rebinds the macro to the model via `bindTo`, which arrow functions ignore.

```php
Product::macro('hasIdentity', function () {
    return $this->exists;
});
// then: $product->hasIdentity()
```

**Add a column** — a migration, nothing else. The column is immediately an attribute (`$guarded = []` keeps it fillable); reads/writes need no model change:

```php
Schema::table('lunar_products', fn (Blueprint $t) => $t->string('external_ref')->nullable());
// then: $product->external_ref
```

**Cast / value-object an added column** — the new seam (this spec). A custom cast class is also how you get an accessor/mutator pair:

```php
Product::extendCasts([
    'metadata'     => AsArrayObject::class,   // $product->metadata is an ArrayObject
    'dimensions'   => DimensionsCast::class,  // custom cast = get/set transform
]);
```

**Constrain every query** — native (`addGlobalScope`, a public static method):

```php
Product::addGlobalScope('inStock', fn (Builder $q) => $q->where('stock', '>', 0));
```

**React to lifecycle** — native (`observe` / `Event::listen`):

```php
Product::observe(ProductObserver::class);
// or: Event::listen('eloquent.saved: '.Product::class, fn (Product $p) => /* … */);
```

**Override behaviour** — not a model concern. Bind the action seam (spec 0029) in the container; the model verb delegates to it:

```php
$this->app->bind(OrderReferenceGenerator::class, MyReferenceGenerator::class);
```

### What is removed

- `Models\Concerns\HasModelExtending` in full — `fireModelEvent` re-dispatch, `newModelQuery`/`replicateInto`, `__callStatic`, the `getTable`/`getForeignKey`/`getMorphClass` parent-walking, `modelClass()` and `isLunarInstance()`. (`morphName()` is **not** substitution — it returns the morph-map alias and is used widely; it is preserved, reimplemented on `Models\Base` against the morph map. `getMorphClass()` falls back to Eloquent's default, which reads the morph map.)
- `modelClass()` and the `Models\Contracts\*` model interfaces. They exist **only** to resolve a model to a contract and look up its swapped class (`guessContractClass()` strips the `Contracts\` segment — its sole consumer). With substitution gone `modelClass()` always returns the Lunar class, so it is dead indirection: internal `X::modelClass()` call sites collapse to `X::class` and the `Models\Contracts\*` interfaces are deleted. (The service-layer interfaces under `Contracts/` — action and manager seams — are a different namespace and are unaffected; they stay.)
- `ModelManifest::replace()` / `add()` / `get()` / `guessContractClass()` and the consumer-directory scanning that registered substitutions. What remains of `ModelManifest`, if anything, is at most a morph-map registrar mapping aliases to concrete classes; that single responsibility can equally move to the service provider.
- The `LUNAR_TESTING_REPLACE_MODELS` matrix leg, `tests/core/Stubs/Models` substitution stubs, and the `extend-models` CI dimension.

### What stays

- The morph map. Aliases (`'product'`, `'collection'`, …) remain, bound **directly to Lunar's own classes**; Eloquent's default `getMorphClass()` reads the map against `static::class`, so the trait's override is not replaced — it is simply deleted. Polymorphism never required substitution.
- All native extension points above, now the documented and only way to extend a model.

## Alternatives considered

- **Keep substitution, guard it with an invariant test.** Add a test asserting each lifecycle event fires its listeners exactly once under extending, for every model using a self-registering trait. Converts silent latent bugs into PR-time failures but keeps the fragile subsystem and its whole surface forever. Cheapest short-term; rejected as the long-term answer because it treats the symptom.
- **A narrower substitution mechanism** (resolution-only canonical classes never instantiated by Lunar internals, so the second identity never boots and the event bridge disappears). Genuinely cleaner than today, but still carries the contract and complexity of substitution for a need the capabilities review shows is narrow. Rejected in favour of removing the capability.
- **Do nothing.** Leave the just-landed dedupe fix in place. Rejected: the fix is itself framework-fighting, and the next trait/operation combination reopens the same class of bug.

## Migration impact

- **Database migrations:** none.
- **Breaking changes to the public contract surface:** yes — this is the headline. `ModelManifest`'s substitution API, the `Models\Contracts\*` interfaces, `Model::modelClass()`, the ability to subclass core models and have Lunar use them, and instances typed as the consumer's class all go away. `HasModelExtending` is removed from `Models/Concerns/`. Any downstream code calling `X::modelClass()` or type-hinting `Models\Contracts\*` retypes to the concrete class (Rector-covered).
- **Upgrade path for v1.x consumers:** Rector rules in the `upgrade` package plus a migration guide that, per the capabilities review, maps each former use to its native replacement: subclass + added relationship → `resolveRelationUsing`; subclass + added method → macro; subclass + added cast/accessor → `extendCasts`; subclass + behaviour override → action binding (0029); subclass + observer/event → `observe()` / `Event::listen`. Substitutions that only existed to surface a column are a no-op (the column is already an attribute). Data migrations remain one-way.
- **Translation / locale impact:** none expected (no new user-facing strings beyond exception messages, which still need all 16 locales if added).
- **Filament / admin impact:** the admin resolves models for resources and relations. Any resolution that went through `modelClass()` points at the concrete Lunar class instead. Swept in slice 1.

## Open questions

- Mechanical scope only (not a question of *whether*): do the `Models\Contracts\*` interfaces appear in any **public method signatures** (Lunar internals, the bridge packages, or documented consumer hooks)? If so, those signatures retype to the concrete class and the change needs a Rector rule. The decision to remove the interfaces is settled; this just sizes the call-site sweep. **Owner: slice 1.**
- Is a separate accessor-injection seam needed, or do custom cast classes cover every real accessor case surfaced in the v1 issue tracker / consumer code? **Owner: review before slice 3.**
- Niche structural needs (per-model connection/table for multi-tenant) — documented as global config, or do we need a sanctioned hook? **Owner: resolve before `accepted`.**
- Should `extendCasts` reject unknown attribute names (typo safety) or stay permissive to allow casting yet-to-exist columns? **Owner: slice 3.**

## References

- `packages/core/src/Models/Concerns/HasModelExtending.php`
- `packages/core/src/Manifests/ModelManifest.php`, `packages/core/src/Facades/ModelManifest.php`
- `tests/core/Unit/Base/Traits/HasModelExtendingTest.php`, `tests/core/Stubs/Models`
- Entry-point conventions and action seams: `[[0029-entry-point-conventions]]`
- Laravel 13 Eloquent docs: getting-started, mutators & casting, serialization

## Implementation plan

Acceptance criterion: every entry under **Replacement recipes** has a passing test, and the native recipes' tests merge **before** the removal slice — they are the gate, not a follow-up. If a recipe cannot be made to pass against a real Lunar model, the removal does not proceed until the gap is resolved (it would reveal a capability substitution provided that natives do not).

- [x] Slice 1 — Recipe coverage. `tests/core/Feature/ModelExtensionRecipesTest.php` exercises each native recipe against real Lunar models: `resolveRelationUsing` (added relationship resolves and loads), model macro (added method callable), migrated column (readable/writable attribute), `addGlobalScope` (constraint applied externally), `Event::listen` (lifecycle fires once), action-contract binding (`$order->cancel()` delegates to the swapped implementation). Proves the capabilities review and becomes the regression net for the removal. The recipes describe the post-removal single-class-identity world, so the test skips under the `extend-models` matrix — and the global-scope recipe's failure there is the defect captured in Problem, not a recipe flaw.
Slice 2 splits in two: the `modelClass()` collapse (mechanical, ~216 explicit call sites, behaviour-preserving today since `modelClass()` already returns the class when nothing is registered) is separated from removing the `Models\Contracts\*` interfaces (~390 signature sites, a wide contract-surface change), so neither lands as an unreviewable diff.

- [x] Slice 2a — Collapse every explicit `Class::modelClass()` call to `Class::class` (and `Class::modelClass()::m()` to `Class::m()`) across all packages, excluding the trait's own `static::modelClass()` and the manifest's dynamic resolve (both removed in slice 3). Verified against the standard (non-extending) suite plus phpstan; the `extend-models` leg is expected to degrade further and is removed in slice 3.
- [x] Slice 2b — Remove the 51 core `Models\Contracts\*` interfaces and retype every reference to the concrete class (Rector RenameClass + facade docblock fixes). Verified: phpstan clean, all CI suites green.
- [x] Slice 3 — Deleted `HasModelExtending` and `ModelManifest`'s substitution API (`replace`/`add`/`get`/`guessContractClass`); morph map registers concrete classes; `morphName()` preserved on `Base`. Removed the substitution test set, the `LUNAR_TESTING_REPLACE_MODELS` harness, the `extend-models` CI dimension, and the orphaned new-static phpstan ignore. Slice-1 recipe tests stay green.
- [ ] Slice 3b — Retire `lunarphp/table-rate-shipping`'s own `Lunar\Shipping\Models\Contracts\*` (6 interfaces) the same way. Independent of core; that package mirrors the deprecated pattern.
- [ ] Slice 4 — Add `HasExtendableCasts` (`extendCasts`) on `Models\Base`, with tests covering casts and custom cast classes on a consumer-added column (the one recipe that cannot precede this slice).
- [ ] Slice 5 — `upgrade` package: Rector rules (including the consumer-facing `Class::modelClass()` -> `Class::class` rewrite, tested with fixtures) and a migration guide mapping each former extending use to its native replacement, cross-checked against the slice-1/4 recipe tests so the guide and the tests cannot drift. Also drop the now-dangling `Lunar\Base\Traits\HasModelExtending` -> `Lunar\Core\Models\Concerns\HasModelExtending` rename in `packages/upgrade/src/Rector/LunarSetList.php` (its target no longer exists).
