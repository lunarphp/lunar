import BundleCard from './components/BundleCard.vue';
import BundleBadge from './components/BundleBadge.vue';
import IncludedInBundlesCard from './components/IncludedInBundlesCard.vue';

// Registered eagerly: slot and cell components must exist before the panel's first render.
window.LunarPanel.registerComponents('bundles', {
    BundleCard,
    BundleBadge,
    IncludedInBundlesCard,
});
