import type {} from '@lunarphp/panel';
import ZonesIndex from './pages/settings/shipping/zones/Index.vue';
import ZonesEdit from './pages/settings/shipping/zones/Edit.vue';
import MethodsIndex from './pages/settings/shipping/methods/Index.vue';
import MethodsEdit from './pages/settings/shipping/methods/Edit.vue';
import ExclusionListsIndex from './pages/settings/shipping/exclusion-lists/Index.vue';
import ExclusionListsEdit from './pages/settings/shipping/exclusion-lists/Edit.vue';
import ShippingDiscountForm from './components/ShippingDiscountForm.vue';

// Registered eagerly: pages must be in the registry before Inertia resolves
// the initial page on a hard load, and the panel's app.ts publishes
// window.LunarPanel before any add-on script runs.
window.LunarPanel.registerPages({
    'shipping::settings/shipping/zones/Index': ZonesIndex,
    'shipping::settings/shipping/zones/Edit': ZonesEdit,
    'shipping::settings/shipping/methods/Index': MethodsIndex,
    'shipping::settings/shipping/methods/Edit': MethodsEdit,
    'shipping::settings/shipping/exclusion-lists/Index': ExclusionListsIndex,
    'shipping::settings/shipping/exclusion-lists/Edit': ExclusionListsEdit,
});

// The discount type form the panel's Discounts section resolves through the
// component registry for the ShippingDiscount type.
window.LunarPanel.registerComponents('shipping', {
    ShippingDiscountForm,
});
