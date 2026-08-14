<?php

namespace Lunar\Blog\Panel;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Blog\Panel\Controllers\BlogArticleController;
use Lunar\Blog\Panel\Controllers\BlogArticleSearchController;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;

/**
 * Blog authoring section for the Lunar Inertia panel. One permission handle
 * (`config('lunar-blog.permission')`) gates the nav item and the routes
 * together. This skeleton wires the nav and the article index only;
 * create/edit, pickers, categories/tags, and the tiptap body land with the
 * full build. See docs/guides/building-a-lunar-panel-addon.md.
 */
class BlogSection extends Section
{
    public function key(): string
    {
        return 'blog';
    }

    public function navigation(NavigationRegistry $registry): void
    {
        $group = config('lunar-blog.navigation.group');
        $item = config('lunar-blog.navigation.item');

        $registry->group($group['key'], $group['label'], priority: $group['priority']);
        $registry->addItem($group['key'], new NavigationItem(
            key: 'blog-articles',
            label: $item['label'],
            // Icon names come from the panel's built-in camelCase set
            // (packages/panel/resources/js/components/Icon.vue); an unknown name
            // renders an invisible SVG with no error.
            icon: $item['icon'],
            route: 'panel.blog.articles.index',
            permission: config('lunar-blog.permission'),
            priority: $item['priority'],
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::middleware('can:'.config('lunar-blog.permission'))
                ->prefix('blog/articles')
                ->name('panel.blog.articles.')
                ->group(function (): void {
                    Route::get('/', [BlogArticleController::class, 'index'])->name('index');
                    Route::get('/create', [BlogArticleController::class, 'create'])->name('create');
                    Route::post('/', [BlogArticleController::class, 'store'])->name('store');
                    // /search (picker data source) is registered before
                    // /{article} so "search" is never captured as a record key.
                    Route::get('/search', BlogArticleSearchController::class)->name('search');
                    Route::get('/{article}/edit', [BlogArticleController::class, 'edit'])->name('edit');
                    Route::patch('/{article}', [BlogArticleController::class, 'update'])->name('update');
                    Route::delete('/{article}', [BlogArticleController::class, 'destroy'])->name('destroy');
                });
        };
    }
}
