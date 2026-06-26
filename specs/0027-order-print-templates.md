# 0027 — Order print templates

- Status: draft
- Author: Glenn Jacobs
- Created: 2026-06-09
- TODO item: Order print templates

## Problem

The order screen has a single **"Download PDF"** button (`DownloadOrderPdfAction`) hardwired to one blade view, `lunarpanel::pdf.order`. That view is styled as an **invoice** (priced, with totals) — but Lunar does not model invoices as a concept, so shipping one as the default printed document is misleading. What a store actually needs from the order screen is a **packing slip / advice note** to drop in the parcel, plus a seam to add its own bespoke documents (returns slip, gift note, or indeed its own invoice if its accounting requires one). Today there is exactly one document, it's the wrong default, and there's no way for a developer to register another short of overriding the action.

The plumbing is already template-agnostic — `DownloadPdfController` validates and renders an arbitrary `view`, and `DownloadPdfAction` exposes `pdfView()` / `filename()`. Only the *entry point* is hardcoded to one template.

## Proposal

Replace the single button with a **"Print" dropdown** (Filament `ActionGroup`, printer icon) whose items are the registered print templates for the order. Each item renders its template through the existing PDF pipeline — no change to the controller or the render mode (`lunar.panel.pdf_rendering` still chooses stream vs download). **The output format remains PDF.**

Core ships **one** default template:

- **Advice Note** — a packing/delivery slip: line items + quantities + shipping & billing addresses. The document that goes in the box.

The existing priced **invoice** view (`lunarpanel::pdf.order`) is **retired** — Lunar doesn't model invoices, so core shouldn't present one as a built-in. A store that wants an invoice registers its own template (see below); core no longer ships that fiction.

The advice note is a **publishable blade view** so a store can restyle it without forking the package.

### Template registry (config-driven)

Templates are declared in config, keyed by an identifier, so developers add their own without touching core:

```php
// config/panel.php (lunar.panel.print_templates)
'print_templates' => [
    'order' => [
        'advice-note' => [
            'label' => 'lunar-filament::actions.orders.print.templates.advice_note',
            'view' => 'lunarpanel::pdf.advice-note',
            'filename' => 'Advice-Note-{reference}.pdf',
        ],
        // Stores needing an invoice register their own view here, e.g.:
        // 'invoice' => [
        //     'label' => 'Invoice',
        //     'view' => 'pdf.my-invoice',
        //     'filename' => 'Invoice-{reference}.pdf',
        // ],
    ],
],
```

- `label` — a lang key or literal string shown in the dropdown.
- `view` — the blade view rendered to PDF (publishable, or any app view).
- `filename` — a pattern; `{reference}` (and other simple record placeholders) are substituted from the order. A developer may register a closure-backed filename in their own action subclass if they need richer logic, but config stays string-only for portability.

A developer registers an extra template by adding a key to `lunar.panel.print_templates.order` and pointing it at their own view — it appears in the dropdown automatically.

### Action

- `DownloadOrderPdfAction` (the existing single action) is superseded on the order page by a **`PrintOrderAction`** `ActionGroup` labelled "Print". It reads `lunar.panel.print_templates.order` and builds one child `DownloadOrderPdfAction` per entry, pre-wired with that template's `view` and `filename`.
- Each child reuses the existing `DownloadPdfAction` machinery, so stream-vs-download behaviour, signed URLs and the `DownloadPdfController` are unchanged.
- With a single registered template (the default advice note) the entry renders as a plain "Print" button; it becomes a dropdown once a store registers more (see open questions).
- `DownloadOrderPdfAction` itself is retained (not removed) so existing consumers and the deprecated `PdfDownload` shim keep working; "Print" is the new composite entry point.

### Advice Note view

A new `packages/admin/resources/views/pdf/advice-note.blade.php`: line items, quantities, SKUs and shipping/billing addresses — the box document. The existing `pdf/order.blade.php` invoice view is **deleted** along with its hardwiring in `DownloadOrderPdfAction`. The advice note is published under the admin views tag so a store can customise it.

## Alternatives considered

- **Keep the invoice as a second built-in template.** Rejected — Lunar doesn't model invoices, so shipping one implies a concept the system doesn't own (no invoice number, date, sequence or tax treatment). Stores that need one register their own view; core stays honest about what it models.
- **Keep one button, add a second button for the advice note.** Rejected — doesn't scale to developer-registered templates and clutters the header; a dropdown is the natural Shopify-like home for "Print → {document}".
- **A PHP template registry/manifest** (à la `CarrierManifest`). Rejected as over-built for what is a label + view + filename triple; plain config is enough and matches `pdf_rendering` already living in panel config. A manifest can come later if templates need behaviour (conditional visibility, per-template data providers).
- **Non-PDF formats (HTML/CSV).** Out of scope — the requirement is explicitly PDF; the registry shape leaves room to add a `format` key later if needed.

## Migration impact

- No database migrations.
- Additive public surface: new `PrintOrderAction` (`ActionGroup`), new `lunar.panel.print_templates` config block, new `advice-note.blade.php` view. `DownloadOrderPdfAction` and `DownloadPdfController` unchanged. The order page swaps `DownloadOrderPdfAction::make()` for `PrintOrderAction::make()` — a visible UI change (button → dropdown) and a **behaviour change**: the default printed document is now the advice note, not the invoice.
- **Removal:** `pdf/order.blade.php` (the invoice view) is deleted. A consumer relying on that view directly (e.g. a custom action calling `pdfView('lunarpanel::pdf.order')`) loses it — v2 is pre-release so this is acceptable, but it's a behaviour change worth calling out in the upgrade notes. Stores wanting an invoice register their own template.
- Publishing: the advice-note view ships under the existing admin views publish tag so `vendor:publish` exposes it for customisation.
- Translations (16 locales): `lunar-filament::actions.orders.print.label` ("Print") plus the template label (`print.templates.advice_note`); content strings inside the advice-note blade.
- Filament / admin: the order header/`ManageOrder` action list replaces the single PDF action with the Print group.

## Open questions

- **Single-template collapse** — out of the box only the advice note is registered, so the proposal renders a plain "Print" button and only becomes a dropdown once a store adds more. Confirm that's the desired feel (vs always a dropdown for consistency).
- **Per-template visibility** — should a template be conditionally hidden (e.g. advice note only when the order has shippable lines)? Config could grow a `visible` closure, but that pushes toward a manifest; defer unless needed.
- **Placeholder set** — which record fields are substitutable in `filename` beyond `{reference}` (`{id}`, `{customer}`)? Define a small documented set.
- **Other resources** — the config is keyed `order` to leave room for product/other print templates later; out of scope here but the shape anticipates it.

## References

- Existing pipeline: `Lunar\Filament\Actions\Support\DownloadPdfAction`, `Lunar\Filament\Actions\Orders\DownloadOrderPdfAction`, `Lunar\Admin\Http\Controllers\DownloadPdfController`, `lunar.panel.pdf_rendering`.
- [[0022-order-fulfilments]] — the order lifecycle work this dropdown sits alongside.
