import ArticlesEditPage from './pages/Blog/Edit.vue';
import ArticlesIndexPage from './pages/Blog/Index.vue';

// Register eagerly at module top level, never inside booting(): pages must be
// registered before Inertia resolves the initial page on a hard load. The page
// keys match the strings the controller passes to Inertia::render(). See
// docs/guides/building-a-lunar-panel-addon.md.
window.LunarPanel.registerPages({
    'blog::Articles/Index': ArticlesIndexPage,
    'blog::Articles/Edit': ArticlesEditPage,
});
