---
name: lunar
description: "Builds e-commerce features with Lunar. Activates when working with products, variants, carts, checkout, orders, discounts, payments, pricing, collections, attributes, tax, or any Lunar model, facade or config. Also activates when the user mentions Lunar, product catalogs, storefront checkout or order management in a Laravel project — even if they don't name Lunar explicitly."
license: MIT
metadata:
  author: lunarphp
---

# Lunar

Headless e-commerce for Laravel. Two packages: `lunarphp/core` (the engine) and
`lunarphp/lunar` (the Filament admin panel).

## Getting current documentation

Do not answer Lunar questions from memory — the API moves between minor versions.
Fetch the docs first:

- **MCP server** (preferred): `https://docs.lunarphp.com/mcp` — full-text search and
  retrieval over the docs, with a `version` parameter (`v1.x`, `v0.x`).
  Add it with `claude mcp add lunar-docs --transport http https://docs.lunarphp.com/mcp`.
- **Index**: `https://docs.lunarphp.com/llms.txt` — every page with a description.
  Append `.md` to any docs URL for the raw markdown.

Boost's `search-docs` tool does **not** cover Lunar. Use the sources above instead.

Start here for common tasks:

| Task | Page |
|------|------|
| Catalog menu, PLP, PDP, cart, checkout, payments, order history | `/1.x/guides/` |
| Models, pricing, taxation, search, attributes, media, URLs | `/1.x/reference/` |
| Custom pipelines, model replacement, driver overrides | `/1.x/extending/` |
| Filament resources, pages, panel config | `/1.x/admin/extending/` |

## Orientation

- Models live in `Lunar\Models`, facades in `Lunar\Facades`.
- Core config is `config/lunar/*.php`; the admin panel's is `config/lunar/panel.php`
  (config key `lunar.panel.*`).
- Tables use the `lunar_` prefix, set by `lunar.database.table_prefix`.

## Gotchas

These are the things that get written wrong on the first attempt.

- **Prices are integers** in the smallest currency unit. $19.99 is `1999`. Monetary
  values come back as `Lunar\DataTypes\Price` — use `->value`, `->decimal()` or
  `->formatted()`, never the object directly.
- **Cart totals are calculated, not stored.** Nothing is persisted until an order is
  created. Call `calculate()` or `recalculate()` before reading `subTotal`, `total`,
  `taxTotal` and friends.
- **Type hint the contracts, not the models.** Lunar resolves models through
  `ModelManifest`, so a user can swap `Lunar\Models\Product` for their own. Public
  method signatures and relationships must use `Lunar\Models\Contracts\*` — there are
  42 of them. Typing a concrete model breaks anyone who has replaced it, and will fatal
  on signature mismatch when extending Lunar's own interfaces (e.g.
  `DiscountTypeInterface::apply(Contracts\Cart $cart): Contracts\Cart`).
- **Add-ons must never replace core models.** Use `Model::resolveRelationUsing()` for
  extra relationships. Replacement is for application code only — two add-ons replacing
  the same model cannot coexist.
- **Never hardcode morph keys.** `'product_variant'` is only the default;
  `lunar.database.morph_prefix` changes it. Use `$model->getMorphClass()`.
- **`attribute_data` needs a `name`.** Lunar and the admin panel both expect it. Values
  are field type objects (`Lunar\FieldTypes\Text`, `TranslatedText`, …), not plain
  strings. Read them back with `translateAttribute()` or `attr()`.
- **Cart failures throw `CartException`.** Catch it and surface `$e->errors()`, which is
  a `MessageBag`. `canCreateOrder()` checks readiness without throwing.
- **One order per cart by default.** `createOrder(allowMultipleOrders: true)` for split
  shipments; `createOrder(orderIdToUpdate: $id)` to update an existing draft.
- **Guard against cart drift before payment.** Take `fingerprint()` at checkout and
  `checkFingerprint()` before capture, or the customer pays the wrong total.
- **`MissingCurrencyPriceException`** means the variant has no price in that currency,
  not that the variant is missing.
- **Setting a shipping address clears `tax_zone_id`.** Pass `clearTaxZone: false` to
  keep an explicit override.
- **Discounts are cached per request.** Call `Discounts::resetDiscounts()` after
  applying or changing a coupon.
- **Collections have no `parent_id`.** Hierarchy is a nested set, so nest with
  `appendNode()` / `makeRoot()` rather than setting a parent column. Every collection
  also needs a `collection_group_id` — there is no default group.
- **Scout needs `soft_delete => true`** in `config/scout.php`, or soft-deleted records
  keep showing up in results.
