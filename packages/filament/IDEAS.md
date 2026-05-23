# Filament Package — Future Ideas

Brainstorm of additions to `lunarphp/filament` that would make it more useful to Filament developers. Not specs, not commitments — a parking lot. Promote an item to `specs/NNNN-…md` when work starts.

Last updated: 2026-05-23. Spec 0009 (items #2 + #4) shipped.

## High-value, idiomatic-Filament additions

1. **`LunarPlugin::make()` installation** — Filament v5 devs expect `$panel->plugin(LunarPlugin::make()->widgets()->resources())`. We register via service provider today; a real plugin object makes opt-in granular and matches what every other Filament package does.
2. **Filament Actions library** — `RefundOrderAction`, `CapturePaymentAction`, `MarkAsShippedAction`, `FulfilLineAction`, `DuplicateProductAction`, `BulkPublishProductsAction`, `AdjustStockAction`. Likely the single biggest gap — commerce is verb-heavy and Filament's action system is where that belongs. _(Shipped in spec 0009.)_
3. **Importer/Exporter classes** — pre-built `ProductImporter`, `CustomerImporter`, `OrderExporter` using Filament's `Importer`/`Exporter` (translated columns, attribute mapping, channel scoping). Very common ask.
4. **Global Search providers** — register Product/Order/Customer/Collection as Filament global search results, respecting Scout when available. Small wiring, big UX. _(Shipped in spec 0009.)_
5. **Test helpers (Pest)** — `actingAsStaff()`, factories for `attribute_data`, assertions like `assertProductSelectIncludes($product)`, helpers for stubbing `RecordSearch`.

## Commerce-specific Filament components

6. **`PriceInput`** — multi-currency price entry in one component, handles the `Price` data type and tax inclusivity. Pairs with the `compare_price → list_price` change.
7. **`VariantMatrix`** — checkbox grid of option values → generated variants, with bulk price/SKU edit. Currently every consumer hand-rolls this.
8. **`AddressFields`** — schema component returning the standard Lunar address block (country + state + lines + postcode) with `CountrySelect`/`StateSelect` wired and dependent.
9. **`ShippingOptionSelect`** — picks from live `ShippingManifest`. Pairs with the TODO item to drop the polymorphic shipping line.
10. **`OrderTimeline` infolist entry** — beyond the activity-log `Timeline`, a status/fulfilment lane view (created → paid → fulfilled → shipped → delivered). Lines up with the state-machine TODO.

## Filament v5 ergonomics we're missing

11. **Standalone resource pages** — `Lunar\Filament\Pages\Products\ListProducts`, etc., so a Filament dev can register them in their own resource without our admin shell. `getXxxFormComponent` covers schema but not pages.
12. **Policies + permission mapping** — default policies that read from `Staff` permissions, opt-in via the plugin. Today downstream apps wire authorization themselves.
13. **Tenancy hooks** — channel-as-tenant support (Filament v5 tenancy contracts), so multi-store SaaS builds don't need to fork.
14. **Table preset filters** — `OrderStatusFilter`, `OrderDateRangeFilter`, `ProductStatusFilter`, `LowStockFilter` as reusable filter classes, not only baked into our tables.
15. **Slideover quick-edit actions** — `EditProductSlideoverAction`, etc. Idiomatic-Filament, currently absent.

## Developer experience

16. **`php artisan lunar:filament:make-resource {Model}`** — scaffold a resource that wires our schemas/tables in the recommended pattern. Lowers the barrier for "use Lunar inside my existing panel."
17. **Extension recipes doc/section** — end-to-end examples of the three customisation strategies ("add a custom tab to the product page", "swap the order table"). README mentions the strategies but doesn't show recipes.
18. **Boost/MCP guidelines for the package** — already on the global TODO ("Add Boost guidelines to packages"); document which Boost tools matter for Filament work (search-docs scoped to filament, etc.).

## Speculative / bigger bets

19. **Admin MCP tools** — already an Idea on TODO; a Filament-aware subset (e.g. "generate a resource that subclasses our Product schema") would land well here.
20. **Customer impersonation action** — admin clicks → opens storefront session as that customer. Common ask, modest scope.
21. **Webhook management UI** — needs a domain decision (core or filament?).

## Suggested next spec

**#3 (Importer/Exporter classes)** — pre-built `ProductImporter`, `CustomerImporter`, `OrderExporter` using Filament's `Importer`/`Exporter` machinery. With actions and global search shipped (spec 0009), the import/export gap is the next-largest "this should already exist" item.
