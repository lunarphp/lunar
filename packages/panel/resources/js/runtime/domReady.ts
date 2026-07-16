/**
 * Resolves once the document has finished parsing and its deferred scripts have run.
 *
 * Add-on bundles are emitted as deferred `<script type="module">` tags (see
 * app.blade.php) and therefore all execute before `DOMContentLoaded`. Gating the
 * panel's first render on this guarantees every add-on registration (pages, slot
 * components, table extensions) is in place before anything resolves or renders —
 * registration is not reactive, so a component registered after first render would
 * otherwise never appear.
 *
 * The `hasFired` flag is essential: after DOMContentLoaded, `document.readyState`
 * stays `'interactive'` until every resource loads, so `readyState` alone can't tell
 * "DCL still pending" from "DCL already fired". This module is imported before DCL
 * (from a deferred module), so the listener below captures the event; callers that
 * run post-DCL then resolve immediately instead of awaiting an event that never fires.
 */
let hasFired = document.readyState === 'complete';

if (!hasFired) {
    document.addEventListener('DOMContentLoaded', () => {
        hasFired = true;
    }, { once: true });
}

export function whenDomContentLoaded(): Promise<void> {
    if (hasFired || document.readyState === 'complete') {
        return Promise.resolve();
    }

    return new Promise((resolve) => {
        document.addEventListener('DOMContentLoaded', () => resolve(), { once: true });
    });
}
