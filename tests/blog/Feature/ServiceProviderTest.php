<?php

use Illuminate\Support\ServiceProvider;
use Lunar\Blog\Models\Article;
use Lunar\Tests\Blog\TestCase;

uses(TestCase::class);

it('boots the blog service provider and merges config', function () {
    expect(config('lunar-blog.permission'))->toBe('blog:manage')
        ->and(config('lunar-blog.media.collection'))->toBe('featured')
        ->and(config('lunar-blog.models.article'))->toBe(Article::class);
});

it('registers the config publish tag', function () {
    $paths = ServiceProvider::pathsToPublish(null, 'lunar-blog-config');

    expect($paths)->not->toBeEmpty();
});
