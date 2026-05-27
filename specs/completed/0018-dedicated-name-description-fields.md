# 0018 — Dedicated `name`, `description` and `short_description` fields

- Status: completed
- Author: Glenn Jacobs
- Created: 2026-05-26
- TODO item: Add `name` and `description` dedicated fields

> **Implementation note (deviation from the proposal below):** Brand's `name`
> stays a **plain, non-translatable `string` column** — the "Leave Brand's
> `name` as a plain string" alternative was chosen over full translatable
> symmetry. Consequently there is **no** Brand `name` string→jsonb conversion
> migration and **no** `$brand->name` property-read Rector rule. Brand still
> gains translatable `description` / `short_description` jsonb columns. Products
> and Collections are implemented exactly as proposed (translatable `name`,
> `description`, `short_description`). The Filament `TranslatedText` component
> binds directly to the new columns with **no synthesizer change** (the
> `ProductOption` name column already proved this path), resolving that open
> question.

## Problem

`Product` and `Collection` have no real `name` or `description`. Both live inside the `attribute_data` jsonb column as `TranslatedText` FieldTypes, seeded as system `Attribute` rows (`handle = 'name'`, `handle = 'description'`, `type = TranslatedText`, `attribute_type = 'product' | 'collection'`) and read through `translateAttribute('name')` (`packages/core/src/Models/Concerns/HasTranslations.php:45`).

`Brand` is a related but distinct case: it already has a dedicated `name` (`brands` migration:13) so it dodges the data-integrity hole — but as a **plain non-translatable `string`**, inconsistent with the translatable name every other catalogue model gets, and it has **no `description` at all**. This spec folds Brand into the same treatment for consistency: a translatable `name` plus `description` and `short_description`.

Storing the two most fundamental fields of an e-commerce catalogue as custom attributes costs us:

- **No real column to query, sort, index or constrain.** Admin product listing sorts/searches against `attribute_data.name` (`packages/filament/src/Tables/Product/ProductTable.php:101`); there is no DB index behind it and no way to add one cleanly. Sorting a catalogue by name means sorting a JSON blob.
- **Every read goes through the FieldType-decoding cast.** `AsAttributeData::get()` (`packages/core/src/Casts/AsAttributeData.php:22`) instantiates a FieldType object per key on every access just to surface a string the model conceptually owns.
- **Name is not guaranteed to exist.** It is one removable `Attribute` row away from disappearing. A product with no name is a data-integrity hole the schema permits.
- **The system attributes pollute the attribute manifest.** `name`/`description` show up in the same list as genuinely custom, type-defined attributes, and the admin form renders them through the generic dynamic `Attributes` component rather than as first-class fields.

These are not custom attributes. They are core, always-present, translatable properties of the model and should be dedicated columns.

A short description (`short_description`) is also worth adding while we are here: catalogue listings, cards and search teasers want a brief translatable summary distinct from the full `description`, which is long-form rich content for the display page. It does not exist today in any form.

`short_description` is scoped narrowly as an **excerpt/teaser**, not as an SEO meta description. A listing teaser ("Handmade in Italy, free returns") and a search-optimised meta description are frequently different content, and overloading one field with both jobs annoys exactly the stores that need them to differ. SEO metadata (`meta_title`, `meta_description`, canonical, …) is deliberately out of scope here and left to a future SEO concept that can sit alongside `HasUrls`; `short_description` is not the meta source.

## Proposal

Add dedicated translatable columns `name`, `description`, and `short_description` to `products`, `collections`, and `brands`, remove the matching system attributes (products/collections), convert Brand's existing plain `name` string to a translatable column, and route all reads through the existing `translate()` helper. `name` is required; `description` and `short_description` are nullable.

### Storage format

Each field is a `jsonb` column holding a locale-keyed map — the same shape v1 stored *inside* the FieldType value:

```json
{ "en": "Trainers", "fr": "Baskets" }
```

This is exactly the shape the existing `translate()` method already reads (`HasTranslations.php:17`), distinct from `translateAttribute()` which decodes the `attribute_data` FieldType wrapper. No new read machinery is needed — `translate('name')` works against the column as-is.

`name` is `jsonb` **not null** (every product/collection must have a name in at least the default locale; enforced at validation). `description` and `short_description` are `jsonb` nullable.

### Schema (baseline — v2 is unreleased)

Edit the baseline migrations directly (consistent with spec 0017's reasoning; the "don't edit the baseline" rule protects shipped schema only).

`packages/core/database/migrations/2026_01_01_000031_create_products_table.php`:

```php
$table->jsonb('name');
$table->jsonb('description')->nullable();
$table->jsonb('short_description')->nullable();
$table->jsonb('attribute_data')->nullable();   // now optional — name/description no longer live here
```

`packages/core/database/migrations/2026_01_01_000021_create_collections_table.php`: same three columns; `attribute_data` becomes nullable.

`packages/core/database/migrations/2026_01_01_000002_create_brands_table.php`: the existing `$table->string('name')` becomes `$table->jsonb('name')`; add nullable `description` and `short_description` jsonb columns. `attribute_data` already nullable.

`attribute_data` stays for genuinely custom attributes; on products/collections it loses its two system keys and therefore its not-null guarantee (a model can now legitimately have no custom attributes).

`product_variants` is **out of scope**: it keeps `attribute_data` and gains no name/description/short_description column. A variant has no name of its own. The only variant change in this spec is a consequential one-line call-site fix in `ProductVariant::getDescription()`, which reads the *parent product's* name (see below) — not a feature addition to variants.

### Core models

`packages/core/src/Models/Product.php`:

- Add `name`, `description`, `short_description` to `$fillable`.
- Cast all three to `AsCollection::class` (`Illuminate\Database\Eloquent\Casts\AsCollection`), matching how `Attribute` already casts its own `name`/`description` metadata.
- `recordTitle()` reads `$this->translate('name')` instead of `$this->translateAttribute('name')`.
- PHPDoc: add `@property ?\Illuminate\Support\Collection $name` etc.; `attribute_data` becomes `?...Collection`.

`packages/core/src/Models/ProductVariant.php` (consequential fix only — variants are out of scope):

- `getDescription()` returns `$this->product->translate('name')` (was `translateAttribute('name')`). Required because the parent Product's name moves to a column; without it the method returns `null`.

`packages/core/src/Models/Collection.php`:

- Same `$fillable` / `$casts` / PHPDoc additions as Product.
- `getBreadcrumbAttribute()` maps ancestors via `$ancestor->translate('name')`.

`packages/core/src/Models/Brand.php`:

- Cast `name`, `description`, `short_description` to `AsCollection::class` (`$guarded = []`, so no `$fillable` change). Brand currently has no cast on `name`.
- PHPDoc `@property string $name` → `@property \Illuminate\Support\Collection $name`; add the two new properties.
- Brand has no `recordTitle()` today; any code reading `$brand->name` as a string (admin record titles, global search, the `deleting` hook does not) now gets a `Collection` and must use `$brand->translate('name')`. This is a real read-side break for Brand — see Migration impact.

The `Contracts\Product` / `Contracts\Collection` / `Contracts\Brand` interfaces gain `name`/`description`/`short_description` as documented properties where they declare the public surface.

### `translate()` hardening

`translate()` (`HasTranslations.php:17`) already resolves locale with a fallback to the app locale then the first value. Confirm it handles an `AsCollection`-cast value (a `Collection` is `ArrayAccess`, so `Arr::accessible()` / `Arr::get()` work). Add a unit test for the column-backed path; it has only ever been exercised against array-cast columns.

`translateAttribute('name')` / `attr('name')` stop resolving once the system attribute is gone — they will return `null` for `name`/`description` (no such key in `attribute_data`). This is the intended break; the Rector rule below migrates callers.

### Seeders / installer

Remove the seeded `name` and `description` system `Attribute` definitions for the `product` and `collection` attribute types from wherever the installer/seeder defines them (the default attribute set). They are no longer attributes.

### Factories

`packages/core/database/factories/ProductFactory.php`:

```php
'name' => collect(['en' => $this->faker->words(3, true)]),
'description' => collect(['en' => $this->faker->paragraph]),
'short_description' => collect(['en' => $this->faker->sentence]),
'attribute_data' => collect(),
```

`CollectionFactory` gains the same `name`/`description`/`short_description` states. Any factory state or test helper that seeded `attribute_data` with a `name`/`description` `Text`/`TranslatedText` moves to the dedicated keys.

`packages/core/database/factories/BrandFactory.php`: `'name' => $this->faker->name()` becomes `'name' => collect(['en' => $this->faker->company])`, plus optional `description`/`short_description` states.

### Admin / Filament

The Product and Collection forms render name/description today only via the generic dynamic `Attributes` component (`packages/filament/src/Forms/Components/Attributes.php`) because they were attribute definitions. They now need explicit, first-class fields.

- **Forms** — add explicit translatable fields to `ProductForm` (`packages/filament/src/Schemas/Product/ProductForm.php`) and `CollectionForm` bound to the `name`, `description`, `short_description` columns, using the existing translatable text component (`packages/filament/src/Forms/Components/TranslatedText.php`). The `Attributes` component stays for the remaining custom attributes only.
- **Binding wrinkle** — the existing `TranslatedText` Filament component + `TranslatedTextSynth` synthesizer were written against `attribute_data` FieldType values (a `TranslatedText` object), not a plain `{locale: value}` column. They must read/write the plain locale-keyed map when bound directly to a column. Either teach the component to accept a column-backed plain map, or add a thin column-bound variant. This is the main implementation cost of the spec (see Open questions).
- **Table column** — `ProductTable` name column changes from `TranslatedTextColumn::make('attribute_data.name')->attributeData()` to `make('name')` reading via `translate('name')`. With a real column, sorting becomes feasible (target the active locale, e.g. `name->>'{locale}'`); add it if cheap, otherwise leave sort off as today.
- **Brand** — `BrandForm::getNameComponent()` (`packages/filament/src/Schemas/Brand/BrandForm.php:36`) changes from `TextInput::make('name')` to the translatable `TranslatedText` field bound to the column; `description`/`short_description` fields added. The Brand table/global-search name display switches from the raw `name` string to `translate('name')`. This is the largest admin delta in the spec, since Brand's name field was previously a simple non-translatable input.

### Search

`ScoutIndexer::mapSearchableAttributes()` (`packages/core/src/Search/ScoutIndexer.php:69`) pulls name/description out of `attribute_data` because they were searchable `Attribute` rows. Once removed, they vanish from the index. Reinstate them as explicit, first-class indexed fields:

- In `ProductIndexer` and `CollectionIndexer`, index `name`, `description`, `short_description` per locale (`name_en`, `name_fr`, …), mirroring how `mapSearchableAttributes()` already explodes `TranslatedText` per locale.
- Brand is `Searchable` too; its indexer (or the generic searchable path) indexes the three new translatable fields per locale, replacing the old single-string `name`.
- Existing indexes need a reindex after deploy (documented, not automated).

### Upgrade package (v1 → v2)

v1 stored name/description inside `attribute_data` as real persisted data and exposed them via `translateAttribute('name')`. Existing databases and consumer code both need migrating.

- **Data migration** — `packages/upgrade/database/migrations/<ts>_add_name_description_columns.php`, picked up by `DataMigrationStep`:
  1. Add the three `jsonb` columns to `products` and `collections` (guard with `Schema::hasColumn`).
  2. Backfill: for each row, copy `attribute_data->'name'->'value'` into `name`, `attribute_data->'description'->'value'` into `description` (the FieldType `value` is already the `{locale: text}` map — see the stored shape in `tests/.../ListProductsTest.php`). `short_description` backfills to `null`.
  3. Strip the `name`/`description` keys from `attribute_data`.
  4. Delete the system `Attribute` rows (`handle IN ('name','description')` for the `product`/`collection` attribute types).
  5. `down()` reverses: re-nest into `attribute_data`, recreate the Attribute rows, drop the columns.
- **Brand `name` string → jsonb** — Brand's `name` was a real persisted `string` column in v1, not an attribute, so it converts rather than backfills-from-`attribute_data`:
  1. Add a temporary jsonb column, copy each `name` string into `{defaultLocale: name}` (default locale from `config`), then swap it into `name` (add-copy-drop-rename, since an in-place type change loses data).
  2. Add `description`/`short_description` jsonb columns; backfill `description` from `attribute_data->'description'->'value'` if v1 seeded a brand description attribute, else `null`.
  3. `down()` collapses `name` back to the default-locale string and drops the added columns.
- **Rector rules** — rewrite reads in consumer code:
  - `$model->translateAttribute('name')` → `$model->translate('name')` (Product/Collection)
  - `$model->attr('name')` → `$model->translate('name')`
  - same for `'description'`.
  - **`$brand->name` (string read) → `$brand->translate('name')`** — Brand's name flips from a plain string to a translatable map, so direct property reads that expected a string break. This is a harder rule: property access is generic, so the rule must scope to `Lunar\Core\Models\Brand` (and the `Lunar\Models\Brand` alias) typed receivers, and bail to a documented manual step where the receiver type can't be inferred. Writes (`$brand->name = 'x'`) likewise need manual review.
  A targeted custom rule set (the handle is a string literal argument, so it can be matched statically for the `translateAttribute`/`attr` cases); register in the `upgrade` package's `config/rector.php` / `LunarSetList`, consistent with spec 0001.

### Tests

- Core: model tests for `translate('name')` against the column; factory states; `ProductVariant::getDescription()`; `Collection` breadcrumb.
- Search: indexer tests asserting `name_en` etc. are present.
- Admin: Product/Collection create+edit tests asserting name/description persist to the columns (replacing the current `attribute_data` JSON assertions, e.g. `ListProductsTest.php:57`).
- Upgrade: backfill migration (round-trips name/description out of and back into `attribute_data`) and the `translateAttribute → translate` Rector rule.

## Alternatives considered

- **Plain `string` columns (not translatable).** Rejected — name/description are translatable across 16 locales today; a flat string would drop existing multilingual data and regress the storefront.
- **Spatie `laravel-translatable` package.** Rejected — adds a dependency for behaviour the existing `translate()` helper already provides against a locale-keyed jsonb column. Keep it in-house and dependency-free.
- **Per-locale columns (`name_en`, `name_fr`, …).** Rejected — 16 locales × 3 fields × 3 tables is a column explosion, and the locale set is configurable per install. A single jsonb map is the right grain.
- **Leave Brand's `name` as a plain string** (add only a translatable `description`). Considered — Brand names are often untranslatable proper nouns, so the conversion buys little for many stores and costs an add-copy-drop-rename migration plus a harder property-read Rector rule. Rejected in favour of full symmetry: a uniform translatable `name` across every catalogue model is worth more than sparing one column a conversion, and stores in transliterating markets (Arabic, CJK) genuinely want translatable brand names.
- **Keep them as attributes but flag them "system/undeletable".** Rejected — still pays the FieldType-decode cost on every read, still unqueryable/unsortable, still no not-null guarantee. Treats the symptom, not the cause.
- **Do nothing.** Rejected — name and description are the catalogue's primary fields; modelling them as removable custom attributes is a standing data-integrity and performance liability, cheapest to fix while v2 is unreleased.

## Migration impact

- **Database migrations**: baseline `products`, `collections` and `brands` migrations edited (fresh v2 installs get the columns; products/collections `attribute_data` becomes nullable; Brand `name` becomes jsonb). One new upgrade migration adds + backfills the columns, removes the product/collection system attributes, and converts Brand's `name` string → jsonb on existing v1 databases.
- **Breaking changes to the public contract surface**: `name`/`description` stop resolving via `translateAttribute()` / `attr()` and resolve via `translate()` instead. `Product`/`Collection`/`ProductVariant`/`Brand` gain (or, for Brand, change) public `name`/`description`/`short_description`. `Brand::$name` changes from `string` to `Collection` — any consumer reading `$brand->name` as a string breaks. The `attribute_data` column on products/collections is now nullable and no longer contains `name`/`description`. v1 consumers covered by the data migration + Rector rules.
- **Upgrade path for v1.x consumers**: handled in the `upgrade` package (data migration + Rector), consistent with spec 0001.
- **Translation / locale impact**: no new lang keys for the *values* (those live in the jsonb map). Admin form/table labels for the new explicit fields need keys in `product.php` / `collection.php` / `brand.php` across all 16 locales in `admin` and `filament` (English first, mirrored). `short_description` is a genuinely new label key, as are Brand's `description`/`short_description`.
- **Filament / admin impact**: Product/Collection forms gain explicit translatable name/description/short_description fields; the `Attributes` component renders only custom attributes. Brand's form name field changes from a plain `TextInput` to the translatable component, and gains description/short_description. Product table name column binds to the real column. The `TranslatedText` form component / synthesizer must support a plain column-backed locale map (main implementation cost). Resources published via `lunar:admin:publish` (spec 0010) pick up the change on re-publish.
- **Search impact**: name/description must be reinstated as explicit indexed fields in the product/collection indexers; existing search indexes require a reindex after deploy.

## Open questions

- **Does `Collection`/`Brand` need `short_description`?** Proposed for full symmetry, but collection and brand listings may not use it. Owner: implementer — confirm against the storefront/admin use case before `accepted`; drop the dead column from a model rather than ship it unused.
- **Brand `$brand->name` property-read Rector.** The string→`Collection` flip is the riskiest consumer break in the spec — generic property access is hard to rewrite reliably. Confirm the Rector rule can scope to typed `Brand` receivers and that the manual-step fallback is clearly documented. Owner: implementer.
- **Filament translatable-column binding.** Exact approach for binding `TranslatedText` to a plain `{locale: value}` column (teach the existing component vs. a column-bound variant) needs a spike against the `TranslatedTextSynth` synthesizer. Owner: implementer.
- **`name` validation.** `name` is not-null at the schema level, but the jsonb map could still be `{}`. Decide where "at least the default locale is present" is enforced — a model-level validation rule / observer, or the form layer only. Owner: implementer.

## References

- TODO.md — "Add `name` and `description` dedicated fields".
- Attribute read path: `packages/core/src/Models/Concerns/HasTranslations.php` (`translate()` :17, `translateAttribute()` :45).
- Cast: `packages/core/src/Casts/AsAttributeData.php`.
- Models: `packages/core/src/Models/Product.php:91` (`recordTitle`), `ProductVariant.php:162` (`getDescription`), `Collection.php:106` (`breadcrumb`), `Brand.php:24` (`@property string $name`).
- Admin: `packages/filament/src/Schemas/Product/ProductForm.php`, `packages/filament/src/Tables/Product/ProductTable.php:101`, `packages/filament/src/Schemas/Brand/BrandForm.php:36`, `packages/filament/src/Forms/Components/TranslatedText.php`.
- Search: `packages/core/src/Search/ScoutIndexer.php:69`.
- Related specs: `[[0001-upgrade-package]]` (data migration + Rector conventions), `[[0013-base-directory-reorganisation]]` (FieldTypes / manifest homes). Sets up the forthcoming "Attributes remodel" TODO item, which this de-risks by removing the two hardest-coded system attributes first.
```