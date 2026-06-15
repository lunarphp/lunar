# 0033 — Multi-tenant homes for this branch's new config

- Status: accepted
- Author: Glenn Jacobs
- Created: 2026-06-15
- TODO item: "Multi-tenancy: relocate the config this branch adds (fulfilment, carriers, order lifecycle) off global config" (surfaced reviewing the fulfilment-methods config seam, [[0031-fulfilment-methods]])

## Scope

This spec covers **only the config this branch introduces** — it is not a sweep of Lunar's existing config. A full review of the pre-existing config surface (and whether to amend the project-wide "config is for values" convention) follows in its own PR.

The new keys, by the commit that added them:

| Config | Added by | Kind |
|---|---|---|
| `fulfilment.methods` (+ `GenericFulfilmentMethod`) | [[0031-fulfilment-methods]] | swap seam |
| `fulfilment.hold_reasons` | [[0022-order-fulfilments]] | value list |
| `shipping.carriers` (+ `GenericCarrier`) | [[0024-shipping-carriers]] | swap seam (with populated defaults) |
| `orders.cancel_reasons` | [[0025-order-cancellation]] | value list |
| `orders.auto_close` | [[0032-auto-close-settled-orders]] | per-store preference |

Explicitly **out of scope** (the follow-up PR): the pre-existing `orders.{reference_generator, reference_format, notifications, pipelines}` and every other config file.

## Problem

Laravel config is **process-global and resolved once** (cached at boot). Lunar targets multi-store (Channels) and multi-tenant (separate-database) hosting, where the answer to "what carriers / hold reasons / cancel reasons does *this* store have, and does it auto-close?" **varies per store**. Config can't express that without **mutating `config([...])` at runtime** per request/tenant — fragile global mutation that defeats config caching and can't register classes.

The five keys above each bake either a swap seam or per-store data into global config. They are **v2 pre-release and not yet shipped**, so this is the moment to relocate them — before they become a contract the follow-up review would have to break.

(The container isn't automatically multi-tenant either — a boot-time singleton is also process-global. The point is the container *can* be made store/tenant-aware cleanly; config can't.)

## Proposal

One lens for the new keys — **swap seams → the container; per-store data → store-scoped (Channel) data; nothing new stays in global config.**

### Swap seams → container

- **`fulfilment.methods` (done, in this PR).** `GenericFulfilmentMethod` and the config key are removed; a method is only ever a class registered against `FulfilmentMethodManifest` from a provider, as core registers `Shipping` / `Collection` / `Digital`. `register()` is `FulfilmentMethod|string`. Loses nothing real: a config method's `claim()` could never assign lines, and its states had to be hand-written `FulfilmentState` classes anyway.
- **`shipping.carriers`.** The identical `Generic*::fromConfig` seam, so the same move: a carrier registers as a class and `CarrierManifest::register()` drops its array/config path. `shipping.php` ships **populated** defaults (`royal-mail`, `dpd`, `ups`, `fedex`), so core registers those in a provider (mirroring `registerCoreMethods()`) instead of config.

### Per-store data → store-scoped

- **`fulfilment.hold_reasons` + `orders.cancel_reasons`.** The same key→label "reason" list, twice; each is only a suggested dropdown plus a key→label lookup, and the models already store any free string and fall back to the raw key. End state: a Channel-scoped, admin-editable reason set. Interim (no DB work yet): a code-level default with a single override seam, so there is no config value to mutate per request.
- **`orders.auto_close`.** A merchant preference that *looks* like a deploy flag — in multi-store it varies per store. End state: a per-store (Channel) preference; interim: a code default + override seam.

## Alternatives considered

- **Ship the keys as-is, mutate config per tenant at runtime** (stancl `ConfigBootstrapper`, middleware rewriting `config()`). Rejected — brittle global mutation, scalar-only, breaks config caching, can't register classes.
- **Leave `hold_reasons` / `cancel_reasons` in config as legitimate "values".** Rejected for the multi-tenant goal — they vary per store, which global config can't express.
- **A registry/manifest for the reason lists.** Acceptable only as the interim default-with-override; a singleton registry is still process-global, so it isn't the store-scoped end state.
- **Do nothing.** Rejected — shipping these as global config makes them a pre-release contract the follow-up review would then have to break.

## Migration impact

- **Database:** none now. The store-scoped reason set / auto-close preference would add Channel-scoped storage when that step is implemented (deferred behind the interim code-default + override seam).
- **Breaking changes (public contract):** removal of `config('lunar.fulfilment.methods')` + `GenericFulfilmentMethod` (done); later `config('lunar.shipping.carriers')` + `GenericCarrier`. All **v2 pre-release** — never shipped — so no Rector rule (same as the other pre-release baseline changes). Consumer guidance: "register in a provider" replaces "add to config".
- **Translation / locale (16 locales):** the `hold_reasons` / `cancel_reasons` English labels live in config today; relocating them changes how they are labelled/translated (admin-entered per store, or lang keys for the code defaults). Carrier labels similarly.
- **Filament / admin:** the hold-reason and cancel-reason dropdowns (and any carrier picker) read from the new source; a future admin screen edits the store-scoped sets.

## Open questions

- **Scoping mechanism.** Channels (rows, one DB), full tenancy (separate DBs), or both? The store-scoped resolver depends on it — likely Channel-scoped, resolved from the active channel.
- **Timing.** Carrier relocation and the reason-lists / preference relocation can land in this PR or just after; the store-scoped DB work is deferrable behind the interim code-default + override seam.
- **Carrier default set.** Where core's batteries-included carriers register — a provider method mirroring `registerCoreMethods()`.

## References

- [[0016-service-layer-di]] — container-for-behaviour / config-for-data; the lens applied here.
- [[0031-fulfilment-methods]] — `fulfilment.methods` + `GenericFulfilmentMethod` (step 1, removed).
- [[0024-shipping-carriers]] — `shipping.carriers` + `GenericCarrier`.
- [[0022-order-fulfilments]] — `hold_reasons`.
- [[0025-order-cancellation]] — `cancel_reasons`.
- [[0032-auto-close-settled-orders]] — `auto_close`.
