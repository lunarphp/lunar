# 0024 — Shipping carriers (tracking service registry)

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-06-08
- TODO item: Order fulfilments follow-up — shipping service registry

## Problem

Spec [[0022-order-fulfilments]] gives fulfilments one-to-many tracking, but each tracking row only stores free-text `tracking_number`, `tracking_url` and `shipping_method`. That means:

- A merchant re-types the **full carrier tracking URL** on every shipment, even though it is always the same template for a given carrier (e.g. `https://track.dpd.co.uk/parcels/<number>`).
- There is no canonical list of the **services** a carrier offers, so "shipping method" is an unconstrained string prone to typos and inconsistency.
- Nothing validates the **format** of a tracking number against the carrier it belongs to.

We want carriers to be a first-class, registered concept that supplies these details once, so recording tracking becomes "pick a carrier, choose a service, enter the number" — the URL is derived.

## Proposal

A small **carrier registry** in core, following the existing manifest pattern (`ShippingManifest`, `FieldTypeManifest`).

### Contract — `Lunar\Core\Contracts\ShippingCarrier`

```php
getKey(): string;                                  // e.g. "royal-mail"
getName(): string;                                 // e.g. "Royal Mail"
getServices(): array;                              // list<string> of service/method names
getTrackingUrl(string $trackingNumber): ?string;   // built from the number, or null
validateTrackingNumber(string $trackingNumber): bool;
```

### Registry — `Lunar\Core\Manifests\CarrierManifest` (`Contracts\CarrierManifest`)

- Bound as a singleton in `LunarServiceProvider::registerServices()`, exposed via the `Lunar\Core\Facades\Carriers` facade.
- `register($carrier)` accepts a `ShippingCarrier` instance, a carrier class string (resolved from the container), or a config array shape.
- `all(): Collection<string, ShippingCarrier>` and `get(?string $key): ?ShippingCarrier` (null-safe).
- On construction it registers every carrier defined in `config('lunar.shipping.carriers')`.

### Config-driven carriers — `Lunar\Core\Shipping\GenericCarrier`

Carriers can be defined entirely in `config/shipping.php` without writing a class:

```php
'carriers' => [
    'royal-mail' => [
        'name' => 'Royal Mail',
        'tracking_url' => 'https://www.royalmail.com/track-your-item#/tracking-results/{tracking_number}',
        'services' => ['Tracked 24', 'Tracked 48', ...],
        'tracking_number_pattern' => '/^.../',   // optional
    ],
    // ...
],
```

`GenericCarrier::getTrackingUrl()` substitutes `{tracking_number}` (URL-encoded) into the template; `validateTrackingNumber()` checks the optional `tracking_number_pattern`. Lunar ships sensible defaults (Royal Mail, DPD, UPS, FedEx) which consumers override by republishing the config. Carriers needing richer logic implement `ShippingCarrier` and are registered via `Carriers::register()` in a service provider — config is for data, the container is for behaviour (per spec 0016).

### Tracking integration

- `fulfilment_trackings` gains a nullable `carrier` column (the carrier key).
- `FulfilmentTracking::carrier(): ?ShippingCarrier` resolves the registered carrier; a derived `url` attribute returns the stored `tracking_url` if present, otherwise `carrier()?->getTrackingUrl(tracking_number)`.
- `ShipFulfilment` and `AddFulfilmentTracking` accept `carrier` and, when both a carrier and a number are present, reject a number that fails `validateTrackingNumber()` (`lunar::exceptions.fulfilment_tracking_invalid_number`).

### Admin

In the ship repeater and add-tracking modal: a **Carrier** select (live) drives a **Shipping method** select populated from that carrier's services and derives the URL, so the `tracking_url` field only appears for the "custom / no carrier" case. The fulfilment card shows the carrier name, the linked (derived) tracking number, and the service.

## Alternatives considered

- **Eloquent `ShippingCarrier` model + admin CRUD** (like Locations). Rejected: carrier behaviour (URL building, number validation) is logic, not data a merchant edits; a class/config registry matches Lunar's manifest convention and avoids a migration + CRUD surface for what is essentially static reference data.
- **Config-only, no registry.** Rejected: no seam for carrier-specific logic and nothing to bind/swap in the container.
- **Do nothing** (keep free-text URL). Rejected: that is the pain this solves.

## Migration impact

- **Database:** one new nullable column `carrier` on `fulfilment_trackings` (added to the 0022 baseline migration on this branch; not yet released, so no separate upgrade migration).
- **Public contract surface:** new `Contracts\ShippingCarrier`, `Contracts\CarrierManifest`, `Manifests\CarrierManifest`, `Facades\Carriers`, `Shipping\GenericCarrier`. Additive — no breaking change. New `Shipping/` concern folder in `packages/core/src`.
- **Upgrade path:** none required for v1.x — fulfilment tracking is new in v2 (0022).
- **Translations (16 locales):** new `lunar::exceptions.fulfilment_tracking_invalid_number`; new admin `order.fulfilments.fields.{carrier,carrier_custom,tracking_url_help}`.
- **Filament:** carrier + service selects in the ship/add-tracking forms; carrier name on the tracking line.

## Open questions

- Delivery-state / tracking-status ingestion (polling carrier APIs for "delivered") is explicitly out of scope — see the `Delivered` note in [[0022-order-fulfilments]]. A future spec can build on this registry.
- Per-service tracking URL templates (some carriers differ by service) are not modelled; the URL is per carrier. Revisit if a real carrier needs it.

## References

- [[0022-order-fulfilments]] — fulfilments & tracking that this extends.
- [[0016-service-layer-di]] — container-for-substitution / config-for-values rule.
