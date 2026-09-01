# Filament Package — Future Ideas

Brainstorm of additions to `lunarphp/filament` that would make it more useful to Filament developers. Not specs, not commitments — a parking lot. Promote an item to `specs/NNNN-…md` when work starts.

Last updated: 2026-05-23. Spec 0009 (items #2 + #4) shipped. Spec 0010 (item #1) shipped; items #11, #12, #16 dropped as superseded.

## High-value, idiomatic-Filament additions

1. **`LunarPlugin::make()` installation** — Filament v5 devs expect `$panel->plugin(LunarPlugin::make()->widgets()->resources())`. We register via service provider today; a real plugin object makes opt-in granular and matches what every other Filament package does. _(Shipped in spec 0010 — covers widgets, global search, actions, Livewire components. No `resources()` method; resources are admin's concern, see spec 0010.)_
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

11. ~~**Standalone resource pages**~~ — _Dropped in spec 0010._ Superseded by the publishable-admin approach: consumers run `php artisan lunar:admin:publish {resource}` to take ownership of the full resource (pages included) rather than re-registering bridge-owned pages.
12. ~~**Policies + permission mapping**~~ — _Dropped in spec 0010._ Staff and its permission infrastructure moved to `lunarphp/core`, so authorization is a core concern, not a bridge concern. Published resources own their own policies.
13. **Tenancy hooks** — channel-as-tenant support (Filament v5 tenancy contracts), so multi-store SaaS builds don't need to fork.
14. **Table preset filters** — `OrderStatusFilter`, `OrderDateRangeFilter`, `ProductStatusFilter`, `LowStockFilter` as reusable filter classes, not only baked into our tables.
15. **Slideover quick-edit actions** — `EditProductSlideoverAction`, etc. Idiomatic-Filament, currently absent.
22. **`AttachAction` on the attribute group relation manager** — attach an existing ungrouped attribute to the group, completing the group-management story that spec 0063's standalone attributes surface started (its edit form's group select already covers regrouping). Deferred open question from spec 0063, leaning yes. _(Numbered out of sequence: existing item numbers are referenced by shipped specs and stay stable.)_

## Developer experience

16. ~~**`php artisan lunar:filament:make-resource {Model}`**~~ — _Dropped in spec 0010._ Superseded by `php artisan lunar:admin:publish {resource}`, which copies admin's real resource into the consumer's namespace rather than scaffolding a subclass of bridge internals.
17. **Extension recipes doc/section** — end-to-end examples of the three customisation strategies ("add a custom tab to the product page", "swap the order table"). README mentions the strategies but doesn't show recipes.
18. **Boost/MCP guidelines for the package** — already on the global TODO ("Add Boost guidelines to packages"); document which Boost tools matter for Filament work (search-docs scoped to filament, etc.).

## Speculative / bigger bets

19. **Admin MCP tools** — already an Idea on TODO; a Filament-aware subset (e.g. "generate a resource that subclasses our Product schema") would land well here.
20. **Customer impersonation action** — admin clicks → opens storefront session as that customer. Common ask, modest scope.
21. **Webhook management UI** — needs a domain decision (core or filament?).

## Suggested next spec

**#3 (Importer/Exporter classes)** — pre-built `ProductImporter`, `CustomerImporter`, `OrderExporter` using Filament's `Importer`/`Exporter` machinery. With the plugin object, actions, and global search now shipped (specs 0009 + 0010), and the v3 migration path established via publishable admin resources, the import/export gap is the next-largest "this should already exist" item.
