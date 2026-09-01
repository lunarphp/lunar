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

// Notifications
export { default as Toaster } from './components/Toaster.vue';
export { useToasts } from './composables/useToasts';
export type { ServerFlash, Toast, ToastApi, ToastTone } from './composables/useToasts';

// Filters / stats
export { default as FilterDropdown } from './components/FilterDropdown.vue';
export { default as KpiCard } from './components/KpiCard.vue';

// Charts
export { default as TimeSeriesChart } from './components/TimeSeriesChart.vue';
export { default as Sparkline } from './components/Sparkline.vue';
export { default as DonutChart } from './components/DonutChart.vue';

// Form inputs
export { default as TextInput } from './components/TextInput.vue';
export { default as Textarea } from './components/Textarea.vue';
export { default as Select } from './components/Select.vue';
export { default as Combobox } from './components/Combobox.vue';
export type { ComboboxOption } from './components/Combobox.vue';
export { default as Checkbox } from './components/Checkbox.vue';
export { default as Toggle } from './components/Toggle.vue';
export { default as FieldLabel } from './components/FieldLabel.vue';
export { default as ColorPicker } from './components/ColorPicker.vue';
export { default as DatePicker } from './components/DatePicker.vue';
export { default as ValuePreviewChip } from './components/ValuePreviewChip.vue';
export type { PreviewValue } from './components/ValuePreviewChip.vue';

// Overlays / display
export { default as Dialog } from './components/Dialog.vue';
export { default as Slideout } from './components/Slideout.vue';
export { default as ConfirmDialog } from './components/ConfirmDialog.vue';
export { default as Tooltip } from './components/Tooltip.vue';
export { default as SideCard } from './components/SideCard.vue';
export { default as Tabs } from './components/Tabs.vue';

// Edit drafts
export { default as DraftActions } from './components/DraftActions.vue';
export { default as DraftConflictDialog } from './components/DraftConflictDialog.vue';
export { useEditDraft } from './composables/useEditDraft';
export type { DraftState, EditDraftForm, EditDraftOptions } from './composables/useEditDraft';
export { DraftConflictError, HttpError, ValidationError, http } from './lib/http';
export type { DraftConflict } from './lib/http';

// Discounts — exported so a third-party discount type's form can reuse the
// same targeting and usage widgets the first-party forms use.
export { default as TargetChipList } from './components/TargetChipList.vue';
export type { TargetChip } from './components/TargetChipList.vue';
export { default as TargetPickerDialog } from './components/TargetPickerDialog.vue';
export type { TargetOption } from './components/TargetPickerDialog.vue';
export { default as UsageMeter } from './components/UsageMeter.vue';

// Primitives
export { default as Button } from './components/Button.vue';
export { default as Icon } from './components/Icon.vue';
