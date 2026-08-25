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

// Notifications
export const Toaster = ui().Toaster;
export const useToasts = () => ui().useToasts();

// Filters / stats
export const FilterDropdown = ui().FilterDropdown;
export const KpiCard = ui().KpiCard;

// Charts
export const TimeSeriesChart = ui().TimeSeriesChart;
export const Sparkline = ui().Sparkline;
export const DonutChart = ui().DonutChart;

// Form inputs
export const TextInput = ui().TextInput;
export const Textarea = ui().Textarea;
export const Select = ui().Select;
export const Combobox = ui().Combobox;
export const Checkbox = ui().Checkbox;
export const Toggle = ui().Toggle;
export const FieldLabel = ui().FieldLabel;
export const ColorPicker = ui().ColorPicker;
export const DatePicker = ui().DatePicker;

// Overlays / display
export const Dialog = ui().Dialog;
export const Slideout = ui().Slideout;
export const ConfirmDialog = ui().ConfirmDialog;
export const Tooltip = ui().Tooltip;
export const SideCard = ui().SideCard;
export const Tabs = ui().Tabs;
export const ValuePreviewChip = ui().ValuePreviewChip;

// Edit drafts
export const DraftActions = ui().DraftActions;
export const DraftConflictDialog = ui().DraftConflictDialog;
export const useEditDraft = (...args) => ui().useEditDraft(...args);
export const http = {
    get: (...args) => ui().http.get(...args),
    post: (...args) => ui().http.post(...args),
    patch: (...args) => ui().http.patch(...args),
    delete: (...args) => ui().http.delete(...args),
};
export const DraftConflictError = ui().DraftConflictError;
export const HttpError = ui().HttpError;
export const ValidationError = ui().ValidationError;

// Primitives
export const Button = ui().Button;
export const Icon = ui().Icon;
