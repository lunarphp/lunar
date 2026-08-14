<?php

use Lunar\Blog\Models\Article;
use Lunar\Blog\Models\Category;
use Lunar\Blog\Models\Tag;
use Lunar\Filament\Models\Staff;

return [
    'models' => [
        'article' => Article::class,
        'category' => Category::class,
        'tag' => Tag::class,
    ],
    'author_model' => Staff::class,
    'permission' => 'blog:manage',
    'media' => [
        'disk' => null,
        'collection' => 'featured',
    ],
    'publish_timezone' => env('LUNAR_BLOG_PUBLISH_TIMEZONE', config('app.timezone')),
    'navigation' => [
        'group' => ['key' => 'blog', 'label' => 'Blog', 'priority' => 30],
        'item' => ['label' => 'Articles', 'icon' => 'fileText', 'priority' => 10],
    ],
];
