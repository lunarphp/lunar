# 0092 — Line actions price from the cart's context

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-10-28
- TODO item: Line actions price from the cart's context — drop the redundant `$customerGroups` parameter from `CalculatesLine` / `CalculatesLineSubtotal` (spec 0092)

## Problem

Both cart line action contracts take the customer groups to price with as a parameter:

```php
interface CalculatesLine
{
    public function execute(
        CartLine $cartLine,
        Collection $customerGroups,
        ?Addressable $shippingAddress = null,
        ?Addressable $billingAddress = null
    ): CartLine;
}

interface CalculatesLineSubtotal
{
    public function execute(CartLine $cartLine, Collection $customerGroups): CartLine;
}
```

Since [[0091-pricing-manager-consumes-storefront-context]], `CalculateLineSubtotal` prices from `$cart->context()`, which already carries the cart's customer groups: the customer's groups, or for a cart with a user but no customer, the merged groups of the user's customers. The parameter now only overrides the context, and only when it is non-empty:

```php
->using($customerGroups->isNotEmpty() ? $context->withCustomerGroups($customerGroups) : $context)
```

So a caller has to work out groups the cart already knows, and two sources of the same fact can disagree: pass a different customer's groups and the line prices for a customer the cart does not belong to. Spec 0091 left the signatures alone because changing two action contracts is a breaking change of its own.

Neither action is used by cart calculation. `$cart->calculate()` prices lines through the `cart_lines` pipeline (`Pipelines/CartLine/GetUnitPrice`). `CalculateLine` calls `CalculatesLineSubtotal`, but nothing in core, the add-on packages or the panel calls `CalculatesLine`; only `tests/core/Unit/Actions/Carts/CalculateLineTest.php` does. They have been standalone since v1 removed `CartLineManager` (#777). They are public seams in `ActionServiceProvider`'s `$actions` map, so consumers may call or rebind them.

## Proposal

Drop `$customerGroups` from both contracts and both implementations. The line prices from its cart's context, the same as every other first-party price.

```php
interface CalculatesLine
{
    public function execute(
        CartLine $cartLine,
        ?Addressable $shippingAddress = null,
        ?Addressable $billingAddress = null
    ): CartLine;
}

interface CalculatesLineSubtotal
{
    public function execute(CartLine $cartLine): CartLine;
}
```

`CalculateLineSubtotal` prices with `using($cart->context())`. `CalculateLine` passes only the line to `CalculatesLineSubtotal`.

A caller that needs different groups for one calculation, such as a quote priced for a trade group, sets them on the cart instead. That is the model's job: `Cart::context()` is the single place a cart's pricing context comes from, and a pricing pipeline reading `getContext()` then sees the same groups the line priced with.

## Alternatives considered

- **Keep the parameter, make it nullable and default it to null.** Non-breaking for callers, but keeps two sources for the same fact and still breaks every class that implements the contracts. Rejected.
- **Replace it with a `?StorefrontContext $context` parameter.** It lets a caller override the whole context per call, but nothing needs that today, and it brings back a second source of pricing state for a line. Revisit if a caller needs it.
- **Delete both actions.** Cart calculation does not use them, so this is the smallest core. Rejected for now: they are documented seams that price one line outside a full cart calculation, and removing them is a larger break than narrowing them. Covered by an open question.
- **Do nothing.** The parameter is harmless when callers pass the cart's own groups, but it invites passing the wrong ones, and it misleads anyone reading the contract about where a line's groups come from.

## Migration impact

- **Database migrations required:** none.
- **Breaking changes to the public contract surface:**
  - `CalculatesLine::execute()` loses its second parameter. Positional calls passing addresses move them one place left.
  - `CalculatesLineSubtotal::execute()` loses its second parameter.
  - Classes implementing either contract, or extending either action and overriding `execute()`, must drop the parameter.
- **Behaviour changes:** a caller that passed groups different from the cart's now gets the cart's. Callers that passed the cart's own groups, or an empty collection, see no change.
- **Upgrade path for v1.x consumers:** an `ArgumentRemoverRector` entry in `LunarSetList` removes the second argument from `execute()` calls on both contracts. Implementations and overrides are not rewritten. `lunar:upgrade` reports them as a manual action, and `WHATS-NEW.md` notes the change.
- **Translation / locale impact:** none.
- **Filament / admin impact:** none. Neither the Filament bridge nor the panel calls the actions.

## Open questions

- **Delete the actions instead?** Cart calculation does not use them. If no consumer use comes up in review, deleting both, with an upgrade note pointing at `$cart->calculate()`, is simpler than narrowing them. Owner: Glenn.

## References

- [[0091-pricing-manager-consumes-storefront-context]] — moved line pricing onto `$cart->context()` and deferred this change.
- [[0016-service-layer-di]] — the action contract and `$actions` map conventions.
- Current surface: `Contracts/Actions/Carts/CalculatesLine.php`, `Contracts/Actions/Carts/CalculatesLineSubtotal.php`, `Actions/Carts/CalculateLine.php`, `Actions/Carts/CalculateLineSubtotal.php`, `ActionServiceProvider.php`, `upgrade/src/Rector/LunarSetList.php`.
- Tests: `tests/core/Unit/Actions/Carts/CalculateLineTest.php`.

## Implementation plan

- [ ] Slice 1 — Drop the parameter from both contracts and actions, and update `CalculateLineTest`, including a test that a line prices with its cart's customer groups.
- [ ] Slice 2 — The `ArgumentRemoverRector` entry, the manual-action note for implementations, and the `WHATS-NEW.md` entry.
