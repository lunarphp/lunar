import type { Component, VNode } from 'vue';

// SettingsShell renders the whole chrome, so a settings page opts out of the
// PanelLayout the panel's resolver auto-applies to add-on pages by declaring a
// no-op persistent layout: the resolver leaves a page with its own layout alone.
// Typed as Inertia's layout render function (h, page) => vnode; the page itself
// is what the resolver renders.
export const noLayout = ((_h: unknown, page: Component) => page as unknown as VNode) as (
    h: (component: Component, children: Component[]) => VNode,
    page: Component,
) => VNode;
