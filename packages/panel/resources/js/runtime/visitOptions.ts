import type { Page, PreserveStateOption, VisitOptions } from '@inertiajs/core';

function preserves(option: PreserveStateOption, page: Page): boolean {
    if (typeof option === 'function') {
        return option(page);
    }

    if (option === 'errors') {
        return Object.keys(page.props.errors ?? {}).length > 0;
    }

    return option;
}

/**
 * Inertia's default visit options for the panel.
 *
 * Non-GET visits (router.post/put/patch/delete) preserve component state by
 * default. When such a visit redirects to another record rendered by the same
 * page component — duplicating a product lands on the copy's edit page — the
 * preserved instance keeps the setup state it built for the previous record:
 * a draft-backed form would keep the old record's values and draft URLs and
 * save to the record staff just left. State is therefore only preserved when
 * the response stays on the current path; landing anywhere else remounts the
 * page, exactly as a link visit to that page does.
 */
export function panelVisitOptions(_href: string, options: VisitOptions): VisitOptions {
    const option = options.preserveState;

    if (!option) {
        return {};
    }

    return {
        preserveState: (page: Page) =>
            new URL(page.url, window.location.href).pathname === window.location.pathname && preserves(option, page),
    };
}
