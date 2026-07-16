// Panel components for add-on pages. Add-on bundles built with
// @lunarphp/panel-vite-plugin externalise this import to the window.LunarPanelUI
// global the panel publishes, so these named exports come from the panel's own
// runtime rather than a bundled copy. This file is the fallback for setups that
// do not externalise the import; it reads the same global lazily.
const ui = () => (typeof window !== 'undefined' ? window.LunarPanelUI : undefined) ?? {};

// Chrome / layout
export const PanelLayout = ui().PanelLayout;
export const SettingsShell = ui().SettingsShell;
export const PageHeader = ui().PageHeader;
export const PageZone = ui().PageZone;
export const Breadcrumbs = ui().Breadcrumbs;

// Data / tables
export const DataTable = ui().DataTable;
export const Pagination = ui().Pagination;
export const PageEmpty = ui().PageEmpty;
export const StatusBadge = ui().StatusBadge;
export const FlashMessage = ui().FlashMessage;

// Filters / stats
export const FilterDropdown = ui().FilterDropdown;
export const KpiCard = ui().KpiCard;

// Charts
export const TimeSeriesChart = ui().TimeSeriesChart;

// Form inputs
export const TextInput = ui().TextInput;
export const Textarea = ui().Textarea;
export const Select = ui().Select;
export const Checkbox = ui().Checkbox;
export const Toggle = ui().Toggle;
export const FieldLabel = ui().FieldLabel;

// Overlays / display
export const Dialog = ui().Dialog;
export const Slideout = ui().Slideout;
export const ConfirmDialog = ui().ConfirmDialog;
export const Tooltip = ui().Tooltip;
export const SideCard = ui().SideCard;
export const Tabs = ui().Tabs;

// Primitives
export const Button = ui().Button;
export const Icon = ui().Icon;
