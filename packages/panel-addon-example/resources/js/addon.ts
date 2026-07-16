import WidgetsIndexPage from './pages/Widgets/Index.vue';
import SettingsIndexPage from './pages/Settings/Index.vue';
import InfoBannerComponent from './components/InfoBanner.vue';

// Register eagerly. The panel's app.ts publishes window.LunarPanel and is emitted
// before any add-on script, so it is always present here. Pages in particular MUST
// be registered before Inertia resolves the initial page on a hard load — deferring
// registration to booting() runs it after the app has already tried (and failed) to
// resolve an add-on page. registerPages/registerComponents only populate registries,
// so they need nothing from a booted app; reserve booting() for post-mount work.
window.LunarPanel.registerPages({
    'example-addon::Widgets/Index': WidgetsIndexPage,
    'example-addon::Settings/Index': SettingsIndexPage,
});

window.LunarPanel.registerComponents('example-addon', {
    InfoBanner: InfoBannerComponent,
});
