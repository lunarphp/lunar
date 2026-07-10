import WidgetsIndexPage from './pages/Widgets/Index.vue';
import InfoBannerComponent from './components/InfoBanner.vue';

// window.LunarPanel is published by the panel's app.ts before it mounts. Add-on
// script tags may execute before or after that happens, so wait for `booting()`
// rather than registering immediately.
window.LunarPanel.booting(() => {
    window.LunarPanel.registerPages({
        'example-addon::Widgets/Index': WidgetsIndexPage,
    });

    window.LunarPanel.registerComponents('example-addon', {
        InfoBanner: InfoBannerComponent,
    });
});
