// Public component surface exposed to add-on bundles. app.ts publishes this as
// window.LunarPanelUI, and @lunarphp/panel-vite-plugin externalises the
// `@lunarphp/panel` import to that global, so add-on pages compose the panel's
// own components without bundling copies. This barrel is the source of truth for
// what add-ons can import; keep the `@lunarphp/panel` package's index.js/index.d.ts
// and the window.LunarPanelUI type (runtime/lunar-panel.d.ts) in sync with it.

// Chrome / layout
export { default as PanelLayout } from './layouts/PanelLayout.vue';
export { default as SettingsShell } from './layouts/SettingsShell.vue';
export { default as PageHeader } from './components/PageHeader.vue';
export { default as PageZone } from './components/PageZone.vue';
export { default as Breadcrumbs } from './components/Breadcrumbs.vue';

// Data / tables
export { default as DataTable } from './components/DataTable.vue';
export { default as Pagination } from './components/Pagination.vue';
export { default as PageEmpty } from './components/PageEmpty.vue';
export { default as StatusBadge } from './components/StatusBadge.vue';

// Filters / stats
export { default as FilterDropdown } from './components/FilterDropdown.vue';
export { default as KpiCard } from './components/KpiCard.vue';

// Charts
export { default as TimeSeriesChart } from './components/TimeSeriesChart.vue';

// Form inputs
export { default as TextInput } from './components/TextInput.vue';
export { default as Textarea } from './components/Textarea.vue';
export { default as Select } from './components/Select.vue';
export { default as Checkbox } from './components/Checkbox.vue';
export { default as Toggle } from './components/Toggle.vue';
export { default as FieldLabel } from './components/FieldLabel.vue';

// Overlays / display
export { default as Dialog } from './components/Dialog.vue';
export { default as Slideout } from './components/Slideout.vue';
export { default as ConfirmDialog } from './components/ConfirmDialog.vue';
export { default as Tooltip } from './components/Tooltip.vue';
export { default as SideCard } from './components/SideCard.vue';
export { default as Tabs } from './components/Tabs.vue';

// Primitives
export { default as Button } from './components/Button.vue';
export { default as Icon } from './components/Icon.vue';
