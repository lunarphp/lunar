# 0070 — First-party payment drivers: Stripe and PayPal

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-08-27
- TODO item: Retire the Opayo package and set the first-party payment driver scope

> Implementation notes (landed): both slices shipped together. Slice 1's driver
> bar went into `CLAUDE.md` only — the documentation site lives in its own
> repository, so the public-facing write-up of the first-party scope and the
> `Payments::extend()` third-party seam is tracked there, not here. Slice 2 also
> reached two places the original table missed: the `lunar-pr-review` skill
> (`SKILL.md` and `references/checklist.md`), which listed `tests/opayo` as a test
> root and `packages/opayo` as a payment-provider review target. No consumer-facing
> code outside `packages/opayo` referenced the package, and the host app never did.

## Problem

The monorepo ships three payment drivers — `stripe`, `paypal`, `opayo` — with no
stated policy on what "first-party driver" means, and no consistent bar any of them
are held to. The result is one maintained driver and two that have quietly rotted.

**Opayo is unmaintained and already broken.** Its tests (`tests/opayo`, 10 tests
across 2 files) are not registered as a testsuite in `phpunit.xml` and not in the CI
matrix in `.github/workflows/tests.yml`, so they have never run in v2. Running them
directly, 6 of 10 fail — `AuthPayloadParameters` gained required constructor
arguments during the v2 refactors and nothing caught it. Beyond the test failures:

- Its `routes/web.php` and blade views (`threed-secure-iframe`,
  `threed-secure-response`, the `PaymentForm` component) target the v1 storefront
  request flow that v2 does not ship. Making it a working v2 driver is a rewrite,
  not a port.
- It is the last package on Laravel Mix, with a committed `dist/opayo.js`;
  everything else has moved to Vite and the npm workspaces at the repo root.
- Opayo is UK/Ireland-only, and Elavon is steering merchants off the legacy Direct
  API that `src/Opayo.php` implements. The rewrite would target a shrinking API.

**PayPal is barely more than a sketch.** 473 lines across 9 files, `tests/paypal` is
a bare `.gitkeep`, no config file, no webhooks, and money bugs in the amount
handling. It is nominally first-party but has none of the guarantees Stripe has.

Downstream consumers have no way to tell any of this from the outside. All three
appear in the root `composer.json` provider list and the package split matrix, which
reads as an equal-support promise the repo does not keep.

## Proposal

State the policy, then make the tree match it.

### The policy

Lunar ships **two** first-party payment drivers: **Stripe** and **PayPal**. Every
other gateway is third-party — a package outside this monorepo, built against the
`Lunar\Core\Contracts\PaymentType` contract and registered with
`Payments::extend()`.

A first-party driver must:

- have a testsuite registered in `phpunit.xml` and an entry in the CI matrix in
  `.github/workflows/tests.yml`;
- test against recorded provider responses (fixtures) rather than the live API, as
  `packages/stripe/resources/responses/*.json` does;
- ship a publishable config file under its own `lunar.<driver>` namespace;
- verify the authorized amount and currency against the order/cart total before
  placing the order;
- scale amounts through `Currency::decimal_places`, never a hardcoded factor;
- implement the full `PaymentType` contract, including `getPaymentChecks()`, or
  document why a method is a deliberate no-op.

`packages/stripe` meets this bar today. `packages/paypal` does not — see
[[0071-paypal-driver-hardening]], which brings it up to it.

This is a scope decision, not a statement about gateway quality. Third-party is a
first-class outcome: the contract is public, `Payments::extend()` is the documented
seam, and nothing about a community driver is second-tier at runtime.

### Removing Opayo

Delete `packages/opayo` and `tests/opayo`, and unwire the references:

| Location | Change |
| --- | --- |
| `composer.json` | Drop the `Lunar\Opayo\` and `Lunar\Tests\Opayo\` psr-4 entries, the `Opayo Payments` name, the `OpayoServiceProvider` entry, and `lunarphp/opayo` from `replace` |
| `.github/workflows/split_packages.yml` | Drop the `opayo` matrix entry |
| `.github/workflows/document-facades.yml` | Drop `Lunar\\Opayo\\Facades\\Opayo` |
| `packages/upgrade/config/upgrade.php` | Drop `2026_01_05_000000_create_opayo_tokens_table` from the disableable-migrations list |
| `CLAUDE.md`, `.claude/triage-rules.md` | Drop `opayo` from the package lists |

The split workflow already mirrors the package to a standalone `lunarphp/opayo`
repository. Removing it here freezes that repository at its last split rather than
deleting anything — the code stays available, and the repo can be archived or handed
to a community maintainer. Nobody's store breaks on upgrade: a merchant still on
Opayo pins the last published tag.

The v1 to v2 upgrade path is unaffected. The `upgrade` package does not transform
`opayo_tokens`; dropping the entry from the disableable-migrations list only stops
v2 from offering to publish a migration that no longer exists. An existing
`opayo_tokens` table is left alone.

## Alternatives considered

**Keep Opayo and fix it.** Rejected. The failing tests are the cheap part; the real
cost is a storefront-flow rewrite plus a migration off the legacy Direct API, for a
gateway we no longer use and whose addressable market is one country.

**Deprecate now, remove after 2.0.** Rejected. v2 is in alpha, which is exactly the
window where removal costs nothing. Deprecating means carrying a package that
demonstrably does not work through a release, then spending a deprecation cycle on
it.

**Keep it in the tree, mark it unsupported.** Rejected. An unsupported package in
the monorepo still appears in the split matrix, the provider list, and the facade
docs. The signal does not reach anyone, and the code keeps breaking silently
because it has no CI.

**Do nothing.** Rejected. The status quo is three drivers with one bar between them,
and no way for a consumer to know which are real.

## Migration impact

- **Database migrations**: none. The `opayo_tokens` migration is removed along with
  the package; existing tables are untouched.
- **Breaking changes**: `Lunar\Opayo\*` is removed from the monorepo. Consumers on
  `lunarphp/opayo` require the standalone package explicitly and pin its last tag.
  No Rector rule — there is no v2 equivalent to rewrite call sites to, and the
  namespace continues to work if the standalone package is installed.
- **Upgrade path**: documented in the v2 upgrade notes as a removed package, with a
  pointer to the standalone repo. No automated step.
- **Translations**: none. `packages/opayo` ships no `resources/lang/`.
- **Filament / panel impact**: none. Nothing in `packages/admin`, `packages/panel`,
  or `packages/filament` references Opayo; drivers are resolved through
  `PaymentManager` by config key.

## Open questions

- Do we archive `lunarphp/opayo` read-only, or offer it up for community
  maintenance with a note in its README? Owner: Glenn, before the removal PR opens.
- Does the docs site need a "third-party payment drivers" page listing known
  community drivers, or is the contract documentation enough? Owner: Glenn, can
  follow the removal.

## References

- [[0071-paypal-driver-hardening]] — brings PayPal up to the bar this spec defines.
- [[0028-line-item-refunds]] — the refund contract every first-party driver
  participates in via `PaymentRefund::$transaction`.
- `packages/core/src/Contracts/PaymentType.php` — the third-party extension contract.

## Implementation plan

- [x] Slice 1 — Write the first-party driver bar into `CLAUDE.md` and the docs, and
      document `Payments::extend()` as the third-party seam.
- [x] Slice 2 — Remove `packages/opayo` and `tests/opayo`, unwire every reference in
      the table above, and confirm the full suite sweep plus phpstan stay green.
