# Remove reward lines the cart no longer earns

- Status: accepted
- Author: Glenn Jacobs
- Created: 2026-11-27
- TODO item: Remove reward lines the cart no longer earns — an automatically added `BuyXGetY` reward stays and is charged at full price once the cart stops earning it; builds on `added_by_discount_id` from spec core-values-out-of-meta (spec remove-unearned-reward-lines)

## Problem

A `BuyXGetY` discount with `automatically_add_rewards` adds a cart line for its reward and sets `added_by_discount_id` on it ([[core-values-out-of-meta]]). After that, nothing changes the line's quantity or removes it. The reward is earned again on every calculation, but only by pricing whatever line is already there. When the cart stops earning the reward, or earns less of it, the line stays and the shopper pays for an item they never added.

Each case below was run against `2.x` at `8ab3f9da`. "Buy A get B" is a `BuyXGetY` with `min_qty` 1, `reward_qty` 1, `automatically_add_rewards` on, A as the condition and B as the reward. Both cost £10 and carry 20% tax.

| Case | Cart after the change | Total | Should be |
| --- | --- | --- | --- |
| Shopper removes A | the added B line alone, not discounted | £12 | £0, empty cart |
| The discount's channel availability ends | A, and the added B line not discounted | £24 | £12 |
| A from 2 down to 1, after B was added at quantity 2 | A, and B at quantity 2 with one unit discounted | £24 | £12 |
| A from 1 up to 3 | A, and B at quantity 1 | £36 | £36, with three B free |
| Shopper sets the added B line to 3 | A, and B at quantity 3 with one unit discounted | £36 | refused, or £12 |

The table covers four separate faults:

- **Nothing reconciles the line.** `BuyXGetY::processAutomaticRewards()` only creates lines. On a later calculation, the added line is just one of the reward lines that `apply()` prices. A discount that no longer applies never reaches `apply()`, whether it ended, its coupon was removed, its conditions fail, its usage limit was reached, or a higher-priority discount with `stop` ran first. So no code that knows the line exists runs for it.
- **An added line earns itself.** `apply()` counts every line as a condition, including lines a discount added. Take a collection condition that contains the reward product, with `min_qty` 2. Adding A at quantity 2 adds B, and A then drops to 1. A plus the added B still count as 2, so the reward keeps itself. The minimum spend has the same loop. `checkDiscountConditions()` sums the `subTotal` of every eligible line, and an added line's subtotal is its full price before the discount. With a £15 minimum spend, A at £10 plus a free B still passes.
- **Raising the condition doesn't raise the reward.** When the cart earns more units than the added line holds, `processAutomaticRewards()` finds the existing line through `$cart->lines->first(...)`. That line isn't in `$addedRewardLines`, so its quantity stays where it is. The breakdown line's quantity rises, but the discount is clamped to the line's subtotal, so the extra units the cart earned are lost.
- **Free items pile up.** `ApplyDiscounts` resets `$cart->discounts` and `$cart->discountBreakdown` before each run but not `$cart->freeItems`. Recalculating the same `Cart` instance three times leaves three entries for one free item.

A deleted discount has its own path. Both `added_by_discount_id` foreign keys are `nullOnDelete`, so a line added by a deleted discount becomes an ordinary shopper line at full price. After that, nothing can tell it was a reward.

## Proposal

One rule: **a line a discount added holds exactly the reward units that discount allocates to it in the current calculation.** Each calculation creates, resizes or removes added lines to match, then prices the cart once more over the lines as they now stand. Added lines belong to the discount that added them, and no discount counts them as the shopper's purchases.

### Added lines are not the shopper's purchases

- `BuyXGetY::apply()` leaves lines with `added_by_discount_id` set out of its condition lines and out of the reward lines it prices in its first pass. Condition quantities and cheapest-first reward allocation see only the shopper's lines.
- `AbstractDiscountType::getEligibleLines()` and `TargetsCartLines::getEligibleLines()` reject lines where `isAddedByDiscount()` is true. The minimum spend in `checkDiscountConditions()` and the lines `PercentageOff` and `FixedAmountOff` discount no longer include them. A line its own discount fully discounts has nothing left for another discount to take off, and its full price no longer counts toward any threshold.

As a result, the reward units any discount earns depend only on the shopper's lines and the discount's own settings. That is what makes the second pricing pass below settle in one step.

### `BuyXGetY` allocates; it doesn't write

`apply()` works out the units it earns from the shopper's condition lines, as it does today. It prices the shopper's own reward lines cheapest first, as it does today. When `automatically_add_rewards` is on, the units left over, the shortfall, go onto lines the discount manages, in this order:

1. **Keep.** The discount's existing added lines, oldest first by id, each keep up to their current quantity until the shortfall is used.
2. **Raise.** Units still left raise the existing added lines, oldest first, as far as `canBeFulfilledAtQuantity()` allows.
3. **Add.** Units still left pick a reward candidate the way `processAutomaticRewards()` does today: at random from the fulfillable rewards, more than one unit on one line, and an out-of-stock candidate skipped. Each new line is a new unsaved `CartLine` with `added_by_discount_id` set.

The discount records each allocation as a `DiscountBreakdownLine` in its breakdown, as it does today. It no longer saves lines or changes their quantity. A new line gets a unit price in the first pass only so that pass's breakdown carries an amount; the second pass prices it like any other line. When `automatically_add_rewards` is off, the shortfall goes nowhere, so the discount's existing added lines get no units. A merchant who turns the setting off sees the added lines go.

Keeping the existing lines first means a gift the shopper has already seen doesn't change product on a later calculation. That matters for a collection reward, which picks at random.

### Reconciling added lines

A new action, `ReconcileRewardLines`, bound to `Lunar\Core\Contracts\Actions\Discounts\ReconcilesRewardLines` in `ActionServiceProvider`, with `execute(Cart $cart): bool`. It is internal orchestration: it gets no verb on `Cart`.

The target for each added line is the sum of the `DiscountBreakdownLine` quantities for that line, in the cart's breakdowns for the discount named by its `added_by_discount_id`. The candidates are the cart's saved lines with `added_by_discount_id` set and the unsaved lines with it set that appear in a breakdown. For each candidate:

- A saved line with a target of 0 is deleted. This covers every way a discount stops applying, because a discount that doesn't run has no breakdown.
- A saved line whose target differs from its quantity gets the new quantity and is saved.
- An unsaved line with a target above 0 is created at that quantity.

The action keeps `$cart->lines` in step with what it wrote and returns whether it wrote anything. Lines are written as models, so the line observer runs. `CalculateCart` sets the cart's calculating flag before the pipeline starts, so the observer doesn't invalidate the totals the calculation is about to persist. That flag already covers the line `processAutomaticRewards()` writes today.

The rule depends only on `added_by_discount_id` and the breakdown, not on `BuyXGetY`. A custom discount type that wants core to manage a line it adds does the same: it puts an unsaved line with `added_by_discount_id` set into its breakdown.

### A second pass when added lines change

`CalculateCart::execute()` runs the `CartCalculation` steps, then calls `ReconcilesRewardLines`. When the action wrote something, `CalculateCart` runs the steps once more over the reconciled lines before calling the `calculated()` modifier hooks and persisting. The cart modifiers' `calculating()` and `calculated()` hooks still fire once per calculation. The line modifier hooks inside `CalculateLines` fire on each pass, as they already do on every `recalculate()`.

The second pass prices added lines like any other line. Shipping, tax and the other discounts then see them too, which the hand pricing in `processAutomaticRewards()` never achieved: today a line added in a run misses that run's shipping. The second pass doesn't reconcile again. Because added lines no longer count toward any discount's conditions, the second pass allocates the same units the first one did, so there is nothing left to change. A custom discount type that breaks this is corrected by the next calculation rather than looping.

The extra pass runs only on a calculation that changes added lines, typically the one where the cart crosses a threshold. A cart served from the persisted snapshot runs no pass at all. Each pass recomputes the snapshot, so the snapshot persisted afterwards is the second pass's.

`ApplyDiscounts` resets `$cart->freeItems` alongside `discounts` and `discountBreakdown`, so each pass, and each recalculation of the same instance, starts empty.

### Deleting a discount

`DiscountObserver::deleting()`, which already tears down the discount's pivots on every delete path, also deletes the cart lines the discount added on carts without a completed order. It uses `chunkById` and deletes one model at a time, so the line observer invalidates each cart's persisted totals. Inside `DeleteDiscount`, this runs in the same transaction as the delete. Lines on carts with a completed order are history and are left alone, and their foreign key is nulled as it is today. Order lines are not touched.

### Shoppers changing an added line

A new validator, `Lunar\Core\Validation\CartLine\CartLineAddedByDiscount`, is appended to `StepList::UpdateCartLineValidation`. It fails when the line being updated has `added_by_discount_id` set and the requested quantity differs from the line's quantity. The message is `lunar::exceptions.carts.line_added_by_discount`: "This item was added by a promotion, so its quantity can't be changed". A meta-only update at the same quantity passes. Without the validator, reconciliation would silently undo the change on the next calculation.

A shopper who wants more of the reward product adds it, and `GetExistingCartLine` already gives it a line of its own. Removing an added line stays allowed. The line comes back on the next calculation while the cart still earns it, as it does today.

### What shoppers see

- A cart that stops earning a reward loses the reward line on its next calculation.
- A cart that earns more units has the added line raised, stock permitting.
- When the shopper adds the reward product themselves, the reward moves onto their line if it is the cheapest reward line. The added line then has no units and goes. The total is the same.
- Two carts merged at sign-in can bring two added lines from one discount. The keep step keeps the older line and the newer one goes once it has no units. Two concurrent calculations that each add a line are fixed the same way.
- Checkout and order creation call `recalculate()`, so an order is never placed with an unearned reward line, whatever the snapshot TTL.

## Alternatives considered

- **Do nothing.** Shoppers are charged for items they didn't add, and the only fix is for them to notice and remove the line.
- **Reconcile inside `BuyXGetY::apply()` only.** A discount that doesn't run never reaches `apply()`: ended, coupon removed, conditions failing, usage limit reached, or stopped by another discount. Those are the common cases. Reconciling has to happen after every discount has had its turn, and it has to be driven by the lines rather than by the discounts.
- **Delete unearned lines without a second pass.** Shipping and the earlier steps have already used the line by then. A deleted line's weight or subtotal stays in the shipping option and in other steps until the next calculation, so the first total after the cart stops earning is still wrong. Hand-pricing the changed lines, as `processAutomaticRewards()` does now, has the same gap for shipping and tax.
- **A loop until nothing changes.** It isn't needed once added lines can't feed any discount's conditions, and a cap would still be needed to protect against custom types. One reconcile and one further pass are enough, and easy to reason about.
- **Keep counting added lines toward conditions and the minimum spend.** A reward then keeps itself earned, as the collection and minimum-spend cases show, and the second pass could allocate differently from the first.
- **Let shoppers change an added line's quantity and treat the extra units as theirs.** The line would then be partly the shopper's and partly the discount's, which [[core-values-out-of-meta]] chose to avoid. The shopper's own line for the same product already exists for this.
- **Cascade-delete cart lines with their discount.** A database cascade skips the line observer, so the cart's persisted totals would not be marked stale. It would also reach carts that already became orders.
- **Remember a declined reward, so a removed gift doesn't come back.** That needs a record per cart of the rewards the shopper declined. It's a separate behaviour change with its own schema, and it's left out here.

## Migration impact

- **Database:** none. `added_by_discount_id` on `cart_lines` and `order_lines` already exists.
- **Breaking changes:**
  - Lines with `added_by_discount_id` set are resized or deleted by every calculation that runs the pipeline. Code that put such a line in a cart itself, expecting it to stay, now sees it removed unless a discount allocates units to it.
  - Lines with `added_by_discount_id` set no longer count toward any discount's conditions, minimum spend or targeted lines. A custom discount type that overrides `getEligibleLines()` without calling the parent keeps its own behaviour, and the upgrade guide says so.
  - `UpdateCartLine` refuses to change the quantity of an added line, through the new validator.
  - Deleting a discount deletes the lines it added on carts without a completed order.
  - A calculation that changes added lines runs the `CartCalculation` steps twice. A custom step with side effects runs twice on that calculation.
  - `BuyXGetY::processAutomaticRewards()` is private and stops saving lines. No public method changes signature, so no Rector rule is needed.
- **Upgrade from v1:** no data changes. Lines from v1 have no `added_by_discount_id` and stay shopper lines.
- **Alpha installs:** nothing to run. The behaviour change gets a row in the alpha upgrade guide's notes for the next release.
- **Translations:** `carts.line_added_by_discount` in `packages/core/resources/lang/*/exceptions.php`, in all 16 locales.
- **Admin:** none. Neither the panel nor the Filament admin edits carts. Order lines already carry `added_by_discount_id`.
- **Docs:** `docs/reference/discounts.mdx`, "Automatically adding rewards": lines follow what the cart earns, the keep, raise and add order, removal when the discount stops applying or is deleted, and the refused quantity change. `docs/extending/discounts.mdx`: how a custom type asks core to manage a line it adds, and that added lines are not eligible lines. `docs/extending/carts.mdx`: the second pass, for authors of calculation steps. A note for storefronts to render added lines without quantity controls, using `isAddedByDiscount()`.

## Open questions

None.

## References

- [[core-values-out-of-meta]]: adds `added_by_discount_id` and leaves this behaviour to this spec.
- [[cart-totals-caching]]: the snapshot, the calculating flag, and `recalculate()` at checkout.
- [[0093-registrable-calculation-steps]]: the `CartCalculation` and `UpdateCartLineValidation` step lists.
- `packages/core/src/DiscountTypes/BuyXGetY.php`, `packages/core/src/DiscountTypes/AbstractDiscountType.php`, `packages/core/src/DiscountTypes/Concerns/TargetsCartLines.php`, `packages/core/src/Actions/Carts/CalculateCart.php`, `packages/core/src/Pipelines/Cart/ApplyDiscounts.php`, `packages/core/src/Observers/DiscountObserver.php`.

## Implementation plan

- [x] Slice 1 — Reconciliation: added lines left out of `BuyXGetY` conditions and first-pass rewards and out of both `getEligibleLines()` implementations; keep, raise and add allocation without writes in `BuyXGetY`; `ReconcilesRewardLines` and its binding; the second pass in `CalculateCart`; `freeItems` reset in `ApplyDiscounts`. Pest tests for each case in the Problem table, the collection and minimum-spend cases, the shopper adding the reward product, a merged cart with two added lines from one discount, the second pass leaving nothing to reconcile, and the cart's shipping option seeing an added line in the calculation that adds it. Existing reward tests that count pipeline runs are updated for the second pass.
- [ ] Slice 2 — Deleting a discount removes its added lines on carts without a completed order, with a test that a cart with a completed order keeps its lines and that the affected cart's totals are invalidated.
- [ ] Slice 3 — `CartLineAddedByDiscount` in `UpdateCartLineValidation`, the translation in 16 locales, and tests for a refused quantity change and an accepted meta-only update.
- [ ] Slice 4 — Docs and the alpha upgrade guide note.
