# 0090 — Remembered guest carts

- Status: accepted
- Author: Glenn Jacobs
- Created: 2026-10-14
- TODO item: Remembered guest carts — a cart cookie outlives the session so a short session lifetime does not lose the cart (spec 0090)

## Problem

`CartSessionManager` finds the current cart only through the session:

```php
$cartId = $this->sessionManager->get($this->getSessionKey());

if (! $cartId && $user = $this->authManager->user()) {
    $cartId = $user->carts()->unmerged()->active()->latest('id')->value('id');
}
```

An authenticated user who comes back after their session expires gets their cart back through the user fallback. A guest does not. Once the session expires the cart ID is gone, and the cart row stays in `carts` until `lunar:prune:carts` removes it.

So a store has to trade cart retention against session storage. A short `session.lifetime` loses guest carts. A long one keeps every visitor's session in Redis (or whichever driver) for that whole time, and crawlers each open a fresh session on every visit, so a long lifetime grows the store quickly.

The cart is already in the database. Getting it back only needs a durable pointer to it that is not the session.

## Proposal

When a guest cart becomes the session's cart, Lunar also sets a long-lived "remember" cookie holding a random token. When the session has no cart, the manager looks the token up and restores the cart into the session. This is the same pattern as Laravel's "remember me" for authentication, applied to carts.

### Schema

`carts` gains one column, folded into the baseline `create_carts_table` migration while v2 is in alpha:

```php
$table->string('remember_token', 64)->nullable()->unique();
```

It stores the SHA-256 hash of the cookie's token, never the token itself, so a database read does not hand out working cookies. `Cart` adds `remember_token` to `$hidden`.

`public_id` is not reused. It is the cart's outward-facing identifier for APIs and webhooks, so it is not a secret, and it cannot be rotated.

### Config

`config/cart_session.php` gains a `remember` block:

```php
'remember' => [
    'enabled' => true,
    'cookie' => 'lunar_cart_remember',
    'lifetime' => 60 * 24 * 30, // minutes
],
```

The cookie takes `path`, `domain`, `secure` and `same_site` from `config('session.*')`, so it follows the same rules as the session cookie. It is always `HttpOnly`.

### Resolution order

`fetchOrCreate()` resolves the cart ID in this order:

1. The session key — as today.
2. The remember cookie — hash the cookie value, then find a cart where `remember_token` matches, `user_id` is null, and the cart is `unmerged()` and `active()`. If nothing matches, queue the cookie to be forgotten.
3. The authenticated user's latest unmerged, active cart — as today.

A cart found through the cookie goes through `use()` like any other, so it is written back into the session and later requests in the session use step 1.

### Refreshing on restore

When step 2 restores a cart, the manager re-queues the incoming cookie value with a fresh `lifetime`. It also calls `$cart->touch()`. This happens once per session, at most. The cookie's expiry therefore slides with each session, not with each request: a guest who returns at least once within `lifetime` keeps their cart indefinitely, without every response carrying a `Set-Cookie` header.

The token is re-sent unchanged rather than rotated. The plaintext is already on the request, so no write is needed. Rotating would also race with parallel requests at the start of a session (XHRs, prefetches). Each of those arrives without a session and with the old cookie, so once one request rotated the token, the others would fail to match and queue the cookie to be forgotten, dropping the cart.

The `touch()` keeps pruning in step with remembering. `PruneAfter` judges activity by `updated_at`, so without it a cart restored every few weeks but never changed would be pruned while its cookie was still being refreshed.

### Issuing and clearing the token

All cookie handling lives in `use()` and `forget()`, so every path that changes the session's cart keeps the cookie in step:

- **`use(Cart $cart)`** with remember enabled:
  - Guest cart (`user_id` null): if the request's cookie already hashes to `$cart->remember_token`, do nothing. That check needs no query, so ordinary requests cost no extra database work. Otherwise generate a 40-character random token, save its hash on the cart, and queue the cookie.
  - Cart with a `user_id`: clear `remember_token` if set, and queue the cookie to be forgotten. Authenticated users already resolve through step 3. A user's cart must never be restorable by a guest cookie, or logging out on a shared device would leave the next visitor with that user's cart, addresses included.
- **`forget()`**: clear `remember_token` on the cart if it still exists (it may have just been soft-deleted under `delete_on_forget`), and queue the cookie to be forgotten.

These rules cover the lifecycle events with no extra wiring:

- Login: `CartSessionAuthListener::login()` calls `associate()` → `use()` with a cart that now has a `user_id`, so the token is cleared.
- Logout: the listener calls `forget()`.
- Order placed: the cart stops being `active()`, so step 2 no longer matches it. The next `use()` call is for the new cart, which gets a fresh token.
- Merge: a merged guest cart has `merged_id` set and fails `unmerged()`.

Issuing a new token replaces the previous hash, so at most one browser holds a working cookie for a cart. If two concurrent requests both issue a token, the last write wins. The other browser's cookie then fails to match, and it gets a new token on its next request.

### Cookie encryption

The token is random and its hash is all the server stores, so encrypting it adds nothing. Encryption would also break the lookup on routes without `EncryptCookies`, such as an API route reading a cookie set on a web route. `LunarServiceProvider` registers the cookie name with `EncryptCookies::except()` at boot, so the raw value is the same on every route.

### Dependency injection

`CartSessionManager` gains two promoted constructor collaborators: `Illuminate\Contracts\Cookie\QueueingFactory` to queue and forget cookies, and `Illuminate\Http\Request` to read the incoming cookie. The manager is already bound `scoped`, so holding the request is safe under Octane. `ServiceLifetimesTest` already guards that binding.

### Bots and `auto_create`

A cookie is issued only when a cart exists. With `auto_create => false` (the default), crawlers that never add to cart get no cart row and no cookie. With `auto_create => true`, every visitor gets both. The config docblock says so, and recommends leaving `auto_create` off when remembering is on.

### Lifetime and pruning

The cookie lifetime should be shorter than `lunar.cart.prune_tables.prune_interval` (90 days by default). Because a restore touches the cart, a cart whose cookie is still live has been active within the last `lifetime` minutes and is not yet eligible for pruning. A pruned cart is harmless anyway: step 2 finds nothing and the cookie is forgotten. The config docblock documents this pairing.

## Alternatives considered

- **Longer session lifetime.** This is the problem as stated. It stores every visitor's session for the whole period, carts or not.
- **Longer session lifetime only for sessions with a cart.** Laravel's session lifetime is a single value for the cookie and the store's TTL. Varying it per session means overriding the session handler and cookie per driver, and the two expiries drift apart.
- **Put the cart ID in the cookie, relying on `EncryptCookies`.** IDs are sequential, so this is safe only on routes where encryption is applied. The ID also cannot be revoked without changing the ID. A random token can be cleared or rotated freely.
- **Reuse `public_id` as the cookie value.** `public_id` is meant to be exposed, so anyone who has seen it could take over the cart.
- **Do nothing.** Stores keep choosing between losing guest carts and storing far more sessions.

## Migration impact

- **Database:** one nullable, unique `remember_token` column on `carts`, folded into `2026_01_01_000041_create_carts_table.php`. The upgrade package's v1 → v2 schema step adds the same column; existing carts start with no token and get one the next time they pass through `use()`.
- **Public contract:** `Contracts\CartSession` does not change. `CartSessionManager`'s constructor gains two parameters, which breaks consumers who subclass the manager and call `parent::__construct()` with positional arguments. Container-resolved subclasses are unaffected. This is noted in the upgrade guide. No Rector rule is needed, because v1 has no equivalent constructor to rewrite against.
- **Behaviour:** on by default. Guest carts now survive session expiry for up to `remember.lifetime`. Setting `remember.enabled` to `false` restores today's behaviour.
- **Translations:** none.
- **Filament / panel:** none. `remember_token` is hidden and is not shown in the admin.

## Open questions

- **Default on or off?** This spec proposes on, since v2 is a major release and the safeguards above keep user carts out of reach. Off would be the more conservative choice for upgraders. Owner: Glenn, decide before `accepted`.
- **Guest addresses on restore.** A restored guest cart keeps any shipping or billing address and email entered at checkout. The exposure is the same as today's session cookie, just for longer, on the same device. Proposal: no special handling, and document it. An option to drop addresses on restore can follow if requested.
- **Header-based token for headless clients.** The same token could be accepted from a request header, so SPAs and mobile apps that do not use cookies can resume carts too. This is out of scope here. It belongs with the storefront API work (`specs/draft-storefront-api.md`).

## References

- `packages/core/src/Managers/CartSessionManager.php` — `fetchOrCreate()`, `use()`, `forget()`
- `packages/core/src/Listeners/CartSessionAuthListener.php`
- `packages/core/src/Console/Commands/PruneCarts.php`, `Pipelines/CartPrune/PruneAfter.php`
- Laravel's `SessionGuard` "remember me" cookie — the prior art for this pattern
- [[0064-scoped-service-lifetimes]] — `CartSession`'s scoped binding

## Implementation plan

- [x] Slice 1 — `remember_token` column, `remember` config block, `EncryptCookies::except()` registration, `CartSessionManager` resolution/issue/clear logic, `Cart::$hidden`. Tests: guest cart restored after the session is flushed; restore re-queues the same token with a fresh expiry and touches the cart; cookie forgotten for a missing, merged, completed or user-owned cart; token cleared on `associate()` and `forget()`; no query or `Set-Cookie` when the cookie already matches; no cookie when remembering is disabled; no cookie for a user-owned cart.
- [x] Slice 2 — upgrade package schema step for the new column (`2026_06_01_000017_add_cart_remember_token`), the `WHATS-NEW.md` entry, and the config docblock covering the pruning pairing and the `auto_create` recommendation.
- [ ] Slice 3 — cart session guide on the docs site.
