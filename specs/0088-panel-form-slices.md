# 0088 — Panel form slices on plain forms

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-09-16
- TODO item: Panel form slices on plain forms — the create pages and settings forms compose the same `FormSlice` contract the drafted edit pages do (spec 0088)

## Problem

[[0086-panel-draft-slices]] lets an add-on add fields to a first-party form's save
through a `FormSlice`, and states the panel's stance: an add-on adds fields under a
namespace it owns, on any first-party form, and never touches the form's own fields.
That spec wires the contract into the drafted edit pages only. The panel's other forms
post a plain Inertia form and redirect: every create page (customers, products, brands,
collections, product types, discounts) and every settings edit form (channels,
currencies, staff, tax zones and the rest). A slot component on any of those pages can
render inputs, but nothing it collects is validated or saved with the form. An add-on
that wants a loyalty tier on the customer create page as well as the edit page has no
path, and the review of 0086 asked, reasonably, whether that would end in a second
contract duplicating `rules`, `commit` and `model`.

It should not. The same slice class must serve both kinds of form.

## Proposal

The create pages and the settings edit forms compose the model's registered form
slices exactly as the drafted edit pages do: the slice's rules validate with the form's
own, its values seed the page, its component binds through `useFormSlice`, and its
`commit()` runs after the form's action inside the same transaction. No new contract,
no new hook, no change to any slice written for 0086.

### What differs from a drafted page

A drafted page holds the whole record's state, so every registered slice is seeded up
front and commits on every save. A plain form carries only what the page posts, so:

- **Binding claims the namespace.** A slice's keys join the form only when a component
  binds to it with `useFormSlice`. A page nobody extends posts exactly what it posts
  today. Product creation, for example, does not suddenly carry the product's channel
  rows.
- **Only submitted namespaces commit.** `FormSlices::commit()` runs a slice's `commit()`
  when the input holds at least one of its keys, with the slice's current values
  overlaid by the submitted ones. Absent namespaces are untouched.
- **Slice rules apply when present.** Each slice rule is composed under `sometimes`, so
  a namespace no component bound cannot fail validation. Within a bound namespace the
  slice's own rules apply in full, `required` included.
- **No draft semantics.** Nothing autosaves, restores or conflict-checks. A slice that
  needs `discard()` is a `DraftSlice`; on a plain form that hook never runs.
- **Create pages have no record yet.** `fields()`, `currentValues()` and `rules()` are
  called with a fresh, unsaved instance of the model to seed and validate; `commit()`
  receives the created record after the store action, inside its transaction.
  First-party slices must tolerate an unsaved record (`ProductAttributeSlice` returns no
  values for a product without a type).

### Server

- `Lunar\Panel\Forms\SliceSet` — the model's registered slices with the prefixing
  logic that today lives in `ComposedDraftResource`: `fields()`, `values()`, `rules()`,
  `labels()`, `partition()`. `ComposedDraftResource` delegates to it, so drafted and
  plain forms share one composer.
- `Lunar\Panel\Forms\FormSlices` — the plain-form service:
  - `rules(string $model, ?Model $record = null): array` — the prefixed rules of every
    slice on the model, each under `sometimes`, against the bound record or a fresh
    instance.
  - `values(Model $record): array` — the prefixed current values, for seeding.
  - `commit(Model $record, array $input): void` — commits the namespaces present in
    the input.
  - `save(array $input, Closure $action): Model` — runs the action and the commit in one
    transaction and returns the record. The line every controller changes to.
- `Lunar\Panel\Http\Requests\Concerns\ValidatesFormSlices` — a form-request trait
  that merges the slice rules for `protected string $sliceModel` into the request's
  own, resolving the record from the route so update requests validate against it. Each
  of the panel's create and settings requests uses it.
- Create controllers pass `formSliceValues` for a fresh instance explicitly, since the
  shared prop resolves from a route-bound record and a create route binds none. Settings
  edit pages get it from the shared prop, which now resolves slices for any model, not
  only draftable ones.

### Client

- `usePanelForm(initial)` in `resources/js/composables/usePanelForm.ts` wraps Inertia's
  `useForm`, returns the same form object, and provides a `SliceForm` under
  `sliceFormKey` whose `claim(namespace)` seeds that namespace's keys from
  `formSliceValues` into the form's data and defaults on first bind. Every create page
  and settings edit page swaps `useForm` for it; nothing else in the page changes.
- `bindFormSlice()` calls `claim()` when the host form offers it, so `useFormSlice`
  works unchanged on both kinds of form. `SliceForm` gains the optional `claim` member.

### Scope

- In: the six create pages and the fourteen settings edit forms with a Lunar model
  behind them. The example add-on places its loyalty card on the customer create page
  as well, so the same slice and component demonstrably serve both.
- Out: the settings index pages' inline create dialogs (they expose no slot zone, so
  there is nothing to bind), the roles form (a Spatie model, not a Lunar one), auth and
  account forms, and the order view.

## Alternatives considered

- **A second contract for plain forms** — rejected; the point of the `FormSlice` /
  `DraftSlice` split in 0086 is that one class serves both.
- **Seed every registered slice into every plain form** — rejected. Product creation
  would post and commit the product's channel and customer-group rows, changing
  behaviour for pages nobody extended. Claim-on-bind keeps unextended pages identical.
- **Commit every slice on every plain-form save** — rejected for the same reason;
  presence in the input is the intent signal a plain form has.
- **A client-side "page saved" event for slot components to persist on** — rejected as
  the mechanism for saving with the form; it lands outside the transaction with ordering
  left to chance. It remains a possible later addition for reacting after a save.
- **Convert the plain forms to drafts** — rejected; create pages have no record to
  draft, and settings forms gain nothing from autosave.

## Migration impact

- **Database**: none.
- **Breaking changes**: none. `SliceForm` gains an optional member. Every plain form's
  request and controller changes internally; routes and payloads are unchanged for a
  page without bound slices.
- **Translations**: none; the example add-on reuses its existing strings.
- **Public contract surface**: `usePanelForm` (exported on `ui.ts`), `FormSlices::rules()`
  / `values()` / `commit()` / `save()`, and the `ValidatesFormSlices` trait, for add-ons
  that compose slices into forms of their own.

## Open questions

- **Field names with dots.** Inertia's form data uses dot paths; a slice field named
  `a.b` would nest on a plain form. First-party fields have none; the docs advise against
  them. Owner: Glenn. Lean: document, do not enforce.

## References

- [[0086-panel-draft-slices]] — the contract and the drafted-page composition.
- [lunar#2741](https://github.com/lunarphp/lunar/pull/2741) — the review that asked for
  one contract across both form kinds.

## Implementation plan

- [x] Slice 1 — Server: `SliceSet` extracted from `ComposedDraftResource`, `FormSlices`
  service, `ValidatesFormSlices` request trait, shared prop resolves slices for any
  model, first-party product slices tolerate an unsaved record; feature tests through a
  create route and a settings route.
- [x] Slice 2 — Client: `usePanelForm` with claim-on-bind, `bindFormSlice` claims, the
  create and settings pages swap to it; vitest coverage.
- [x] Slice 3 — Controllers and requests: the six create and fourteen settings pairs
  compose through `FormSlices::save()`; the example add-on's loyalty card on the
  customer create page with an end-to-end test; README section.
