import IndexPage from './pages/Index.vue';
import QueryPage from './pages/Query.vue';
import ProductPage from './pages/Product.vue';
import SettingsIndexPage from './pages/Settings/Index.vue';
import SearchConversionWidget from './components/SearchConversionWidget.vue';
import ProductSearchPerformance from './components/ProductSearchPerformance.vue';

// Registered eagerly: pages must exist before Inertia resolves the initial page.
window.LunarPanel.registerPages({
    'search-relevance::Index': IndexPage,
    'search-relevance::Query': QueryPage,
    'search-relevance::Product': ProductPage,
    'search-relevance::Settings/Index': SettingsIndexPage,
});

window.LunarPanel.registerComponents('search-relevance', {
    SearchConversionWidget,
    ProductSearchPerformance,
});
