# Typed fields for the core values still kept in meta

- Status: proposed
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

When a `BuyXGetY` discount has `automatically_add_rewards` set, `processAutomaticRewards()` adds a cart line for the reward and records it in the line's `meta`:

```php
$meta->added_by_discount[$this->discount->id] = 1; // or ++
$rewardLine->meta = $meta;
$rewardLine->save();
```

- **It changes line identity.** `GetExistingCartLine`, `CreateOrderLines` and `MergeCart` treat two lines as the same when their purchasable and `meta` match. A reward line carries `added_by_discount`, so when the shopper adds the same product, it doesn't merge with the reward line and the cart shows two lines for one product. The behaviour depends on a core value sitting in the store's bag.
- **Nothing reads it.** The only other mention is `docs/reference/discounts.mdx`. Core records which lines it added but never uses the record.
- **Added lines are never taken back.** When the cart stops qualifying (the shopper removes the item that earned the reward, or the discount ends), the line core added stays in the cart and is charged at full price. A shopper who never chose that product pays for it unless they notice. Doing better needs core to know which lines it added, which is what the meta key was meant to record.

## Proposal

### Option value colours

- `product_option_values.colour`: nullable string, 7 characters, holding `#RRGGBB` in upper case. Added to the baseline migration while v2 is in alpha.
- `ProductOptionValue` gains `@property ?string $colour`.
- `UpdateProductOption` writes `colour` when the option's type is `Colour` and the row has a colour, and sets it to null otherwise. `clearValuePayloads()` sets it to null on a type change. Neither touches `meta`.
- `UpdateProductOption` validates the format before saving: `#` followed by three or six hex digits, the same rule as the panel's `ProductOptionRequest`. It stores the six-digit upper-case form, expanding `#abc` to `#AABBCC`, and throws `ProductOptionActionException` with a translated message on a bad value.
- The `ProductOptionType` docblock describes the `colour` column.
- The four panel controllers read `$value->colour`.

The panel's `ProductOptionRequest` already checks the colour on the way in. The action's check covers callers that use the action directly.

### Reward lines added by a discount

- `cart_lines.added_by_discount_id`: nullable foreign key to `discounts`, null on delete. Set on lines core adds, and only those. A line the shopper added stays null, even when a discount prices it at zero.
- `CartLine` gains `@property ?int $added_by_discount_id`, an `addedByDiscount(): BelongsTo` relation, and `isAddedByDiscount(): bool`.
- `BuyXGetY::processAutomaticRewards()` sets `added_by_discount_id` when it creates the line and stops writing `meta`.
- **Line identity.** `GetExistingCartLine` skips lines with `added_by_discount_id` set, so a product the shopper adds gets its own line, never one core manages. `MergeCart` keeps added lines separate in the same way. Identity no longer depends on a core value in `meta`.
- **Removal.** After the discounts have been applied, `CalculateCart` checks each line with `added_by_discount_id`. A line whose discount didn't reward it in this calculation is deleted. The check runs after every discount has had its turn, so a line that another reward rule still covers stays. `HydrateCartTotals` and `PersistCartTotals` see the cart as it is after the removal.
- **Order lines.** `order_lines.added_by_discount_id`: nullable foreign key to `discounts`, null on delete. `CreateOrderLines` copies it from the cart line, and its existing-line match (used when a draft order is rebuilt) compares it alongside the purchasable and `meta`. Without it, a shopper's line and a reward line for the same product would match the same order line once the meta key is gone, and one would overwrite the other. Staff can also see on the order which lines were added by a discount.

The removal happens during cart calculation, which is where the line was added, so a cart is never shown with a stale reward. A store that wants the old behaviour (keep the line and charge for it) can unset `added_by_discount_id` on the line it wants to keep.

## Alternatives considered

- **Delete the `added_by_discount` meta key and track nothing.** It fixes line identity, but core still can't tell its own lines from the shopper's, so the stale-reward problem stays and can't be fixed later without this column.
- **A JSON `added_by_discount` column mirroring the meta shape (discount id to quantity).** A line is added by one discount, and the quantity core added is the line's quantity, since core only raises the quantity on lines it created in the same run. A single foreign key says the same thing, can be queried, and is cleaned up when a discount is deleted.
- **Keep the reward line merged with the shopper's line.** Merging makes the line ambiguous: removing the reward would have to know how much of the quantity was the shopper's. Keeping core's line separate keeps removal exact.
- **Store the option colour in the value's `name` translations or a media item.** Neither is a colour: one is translated text and the other is for the swatch type's image.

## Migration impact

- **Database:** `product_option_values.colour`, and `added_by_discount_id` on `cart_lines` and `order_lines`, in the baseline migrations.
- **Breaking changes:**
  - `meta['colour']` on option values is no longer written or read by core. Code reading it moves to `$value->colour`.
  - `meta['added_by_discount']` on cart lines is no longer written. Code reading it moves to `$line->added_by_discount_id`.
  - Automatically added reward lines are removed when the cart no longer earns them. Before, they stayed and were charged.
  - A product the shopper adds no longer merges into a reward line.
- **Upgrade from v1:** v1 has no colour option type, so there is nothing to move. v1 `BuyXGetY` didn't add lines with this marker. No data step. The upgrade guide notes the removal behaviour.
- **Translations:** the colour format message in `core`, in all 16 locales.
- **Admin:** the four panel controllers. Filament doesn't read either value.
- **Docs:** `docs/reference/discounts.mdx` describes `added_by_discount_id` and removal in place of the meta key. The product options reference describes the `colour` column.

## Open questions

- **Removal and stock.** If an added reward can no longer be fulfilled at its quantity, should core reduce it to what can be fulfilled, or leave it to the existing cart validators? This spec leaves fulfilment to the validators. Owner: Glenn.

## References

- `CLAUDE.md`, "No `meta` in core" (#2859, #2860).
- [[0083-cart-line-fulfilment-method]]: removes the shipping line's `meta['pickup']`.
- [[discount-coupons]]: also changes discount bookkeeping. Neither spec touches the other's tables.
- `packages/core/src/Actions/ProductOptions/UpdateProductOption.php`, `packages/core/src/Enums/ProductOptionType.php`, `packages/core/src/DiscountTypes/BuyXGetY.php`, `packages/core/src/Actions/Carts/GetExistingCartLine.php`, `packages/core/src/Actions/Carts/MergeCart.php`.

## Implementation plan

- [ ] Slice 1 — Option value colours: the column, model property, `UpdateProductOption` and its validation, the enum docblock, the four panel controllers, the translation. Tests.
- [ ] Slice 2 — Reward lines: the two columns, model properties and relations, `BuyXGetY` writing it, line identity in `GetExistingCartLine` and `MergeCart`, `CreateOrderLines` copying it and matching on it. Tests, including a shopper adding the reward product and a draft order rebuilt with both lines.
- [ ] Slice 3 — Removing reward lines the cart no longer earns, in `CalculateCart`. Tests, including two discounts rewarding the same product.
- [ ] Slice 4 — Docs and upgrade guide.
