# Typed fields for the core values still kept in meta

- Status: accepted
- Author: Glenn Jacobs
- Created: 2026-11-12
- TODO item: Typed fields for the core values still kept in meta — option value colours and reward lines added by a discount (spec core-values-out-of-meta)

## Problem

`meta` columns exist for store implementations and customisations. Core never reads or writes its own keys in them. It may carry a store's `meta` through without interpreting it. Two places in `packages/core` still break the rule. ([[0083-cart-line-fulfilment-method]] removes a third, the shipping line's `meta['pickup']`.)

### Option value colours

A product option of type `ProductOptionType::Colour` gives each value a hex colour. Core stores it in the value's `meta`:

- `UpdateProductOption` writes `meta['colour']` when saving a colour option's values, and `clearValuePayloads()` removes it when the option's type changes.
- The `ProductOptionType` docblock documents `meta->colour` as where the colour lives.
- Four panel controllers read `$value->meta['colour']` (`ProductOptionSearchController`, `ProductEditController`, `ProductOptionEditController`, `ProductOptionIndexController`).

The colour is a core value: the option type defines it, core writes it, and every surface that renders a colour option needs it. It has no type, no validation at the column, and it shares a bag with whatever a store puts there. A store that uses `meta['colour']` for its own purpose has it overwritten or removed by core.

### Reward lines added by a discount

When a `BuyXGetY` discount has `automatically_add_rewards` set, `processAutomaticRewards()` records the reward in the line's `meta`:

```php
$meta->added_by_discount[$this->discount->id] = 1; // or ++
$rewardLine->meta = $meta;
$rewardLine->save();
```

- **It changes line identity.** `GetExistingCartLine`, `CreateOrderLines` and `MergeCart` treat two lines as the same when their purchasable and `meta` match. A reward line carries `added_by_discount`, so when the shopper adds the same product, it doesn't merge with the reward line and the cart shows two lines for one product. The behaviour depends on a core value sitting in the store's bag.
- **It marks lines core didn't add.** When the reward's purchasable is already in the cart, `processAutomaticRewards()` picks the shopper's line (`$cart->lines->first(...)` on the purchasable) and writes the key onto it, so the key doesn't say which lines core added.
- **Nothing reads it.** The only other mention is `docs/reference/discounts.mdx`.

A `cart_line_discount` pivot and `CartLine::discounts()` already exist, carried over from v1. Nothing in core reads or writes them, and the baseline migration constrains `cart_line_id` to `carts` instead of `cart_lines`.

## Proposal

### Option value colours

- `product_option_values.colour`: nullable string, 7 characters, holding `#RRGGBB` in upper case.
- `ProductOptionValue` gains `@property ?string $colour`.
- `UpdateProductOption` writes `colour` when the option's type is `Colour` and the row has a colour, and sets it to null otherwise. `clearValuePayloads()` sets it to null on a type change. Neither touches `meta`.
- `UpdateProductOption` validates the format before saving: `#` followed by three or six hex digits, the same rule as the panel's `ProductOptionRequest`. It stores the six-digit upper-case form, expanding `#abc` to `#AABBCC`, and throws `ProductOptionActionException` with a translated message on a bad value.
- The `ProductOptionType` docblock describes the `colour` column.
- The four panel controllers read `$value->colour`.

The panel's `ProductOptionRequest` already checks the colour on the way in. The action's check covers callers that use the action directly.

### Reward lines added by a discount

This spec moves the record out of `meta` and keeps core's lines apart from the shopper's. It doesn't change when a reward line is added, priced or kept.

- `cart_lines.added_by_discount_id`: nullable foreign key to `discounts`. Set on lines core adds, and only those. A line the shopper added stays null, even when a discount prices it at zero.
- `CartLine` gains `@property ?int $added_by_discount_id`, an `addedByDiscount(): BelongsTo` relation, and `isAddedByDiscount(): bool`.
- **Which lines get it.** `BuyXGetY::processAutomaticRewards()` sets `added_by_discount_id` on a line it creates and stops writing `meta`. When the reward's purchasable is already on a line the shopper added, it prices that line as it does today and leaves the column null, so the shopper's line never becomes core's.
- **Line identity.** `GetExistingCartLine` skips lines with `added_by_discount_id` set, so a product the shopper adds gets its own line, never one core manages. Identity no longer depends on a core value in `meta`.
- **Merging carts.** `MergeCart` matches a source line to a target line only when `added_by_discount_id` is equal as well as the purchasable and `meta`, and copies `added_by_discount_id` when it creates a target line from a source line. A guest's reward line stays a reward line after sign-in, and never merges into the shopper's line for the same product.
- **Order lines.** `order_lines.added_by_discount_id`: nullable foreign key to `discounts`. `CreateOrderLines` copies it from the cart line, and its existing-line match (used when a draft order is rebuilt) compares it alongside the purchasable and `meta`. Without it, a shopper's line and a reward line for the same product would match the same order line once the meta key is gone, and one would overwrite the other. Staff can also see on the order which lines were added by a discount.
- **Deleting a discount.** Both foreign keys are null on delete. The cart line stays in the cart as an ordinary line, as it does today when the meta key outlives its discount. An order line keeps its history and loses only the link. Removing reward lines the cart no longer earns is left to its own spec (see below).
- **The `cart_line_discount` pivot.** The table and `CartLine::discounts()` are removed. `added_by_discount_id` records the only line-to-discount link core needs, and the discount breakdown already records which discounts priced a line.

### Out of scope: removing reward lines the cart no longer earns

An added reward line stays in the cart when the cart stops earning it, and is then charged at full price. Fixing that needs `added_by_discount_id`, which this spec adds, and has its own questions: lines the cart earns in part, a shopper changing the quantity of a reward line, which line a reward is allocated to, and where in the calculation the removal runs. It's tracked as its own TODO item.

## Alternatives considered

- **Delete the `added_by_discount` meta key and track nothing.** It fixes line identity, but core can't tell its own lines from the shopper's, so removing reward lines the cart no longer earns can't be done later without this column.
- **A JSON `added_by_discount` column mirroring the meta shape (discount id to quantity).** A line is added by one discount, and core only raises the quantity on lines it created in the same run. A single foreign key says the same thing, can be queried, and is cleaned up when a discount is deleted.
- **Reuse the `cart_line_discount` pivot.** It's many-to-many, and a line is added by at most one discount. It also doesn't reach order lines. A pivot row per line would be one more table to keep in step when lines merge or orders are rebuilt.
- **Cascade-delete cart lines with their discount.** It removes reward lines, which is the behaviour change this spec leaves out. A database cascade also skips the line observer, so the cart's persisted totals wouldn't be marked stale.
- **Keep the reward line merged with the shopper's line.** Merging makes the line ambiguous: anything that later acts on core's lines would have to know how much of the quantity was the shopper's.
- **Store the option colour in the value's `name` translations or a media item.** Neither is a colour: one is translated text and the other is for the swatch type's image.

## Migration impact

- **Database:** `product_option_values.colour`, and `added_by_discount_id` on `cart_lines` and `order_lines`, added to the baseline migrations while v2 is in alpha. The `cart_line_discount` baseline migration is removed.
- **Breaking changes:**
  - `meta['colour']` on option values is no longer written or read by core. Code reading it moves to `$value->colour`.
  - `meta['added_by_discount']` on cart lines is no longer written. Code reading it moves to `$line->added_by_discount_id`.
  - A product the shopper adds no longer merges into a reward line.
  - `CartLine::discounts()` is removed, with no replacement. The upgrade guide lists it. No Rector rule: the `upgrade` package's rules rewrite a call into its replacement, and this relation has none.
- **Upgrade from v1:** the ledger rewrite marks the baseline migrations as run, so an upgraded database gets the schema change from an `upgrade` package migration: add the three columns and drop `cart_line_discount`, each guarded so re-runs are no-ops. The table is dropped only when it's empty; a store that wrote rows to it keeps the table, and the upgrade guide says so. The `create_cart_line_discount_table` entry comes out of the baseline list in `packages/upgrade/config/upgrade.php` along with the migration. No data is moved: v1 has no colour option type, and v1 `BuyXGetY` didn't add lines with this marker.
- **Translations:** the colour format message in `core`, in all 16 locales.
- **Admin:** the four panel controllers. Filament doesn't read either value.
- **Docs:** `docs/reference/discounts.mdx` describes `added_by_discount_id` in place of the meta key. `docs/reference/products.mdx` adds `colour` to the `ProductOptionValue` field table and drops the advice to keep colour hex values in `meta`.

## Open questions

None.

## References

- `CLAUDE.md`, "No `meta` in core" (#2859, #2860).
- [[0083-cart-line-fulfilment-method]]: removes the shipping line's `meta['pickup']`.
- [[discount-coupons]]: also changes discount bookkeeping. Neither spec touches the other's tables.
- `packages/core/src/Actions/ProductOptions/UpdateProductOption.php`, `packages/core/src/Enums/ProductOptionType.php`, `packages/core/src/DiscountTypes/BuyXGetY.php`, `packages/core/src/Actions/Carts/GetExistingCartLine.php`, `packages/core/src/Actions/Carts/MergeCart.php`, `packages/core/src/Pipelines/Order/Creation/CreateOrderLines.php`.

## Implementation plan

- [ ] Slice 1 — Option value colours: the column, model property, `UpdateProductOption` and its validation, the enum docblock, the four panel controllers, the translation, the upgrade migration. Tests.
- [ ] Slice 2 — Reward lines: the two columns, model properties and relations, `BuyXGetY` writing it only on lines it creates, line identity in `GetExistingCartLine`, `MergeCart` matching and copying it, `CreateOrderLines` copying it and matching on it, removing `cart_line_discount` and `CartLine::discounts()` and its entry in `packages/upgrade/config/upgrade.php`, the upgrade migration. Tests, including a shopper adding the reward product, a guest cart with a reward line merged on sign-in, and a draft order rebuilt with both lines.
- [ ] Slice 3 — Docs and upgrade guide.
