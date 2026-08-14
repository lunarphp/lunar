<?php

namespace Lunar\Blog;

use Illuminate\Support\ServiceProvider;
use Lunar\Blog\Panel\BlogSection;
use Lunar\Panel\Facades\Panel;
use Lunar\Panel\PanelManager;

class BlogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/lunar-blog.php', 'lunar-blog');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'lunar-blog');

        $this->publishes([
            __DIR__.'/../config/lunar-blog.php' => config_path('lunar-blog.php'),
        ], 'lunar-blog-config');

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'lunar-blog-migrations');

        $this->registerPanel();
    }

    protected function registerPanel(): void
    {
        Panel::section(new BlogSection);

        $this->app->make(PanelManager::class)->vite('lunar-blog', [
            'input' => 'resources/js/addon.ts',
            'hotFile' => null,
            'buildDirectory' => 'vendor/lunar-panel/lunar-blog',
            '__buildSourcePath' => dirname(__DIR__).'/build',
        ]);
    }
}
