<?php

use Lunar\Blog\Models\Article;
use Lunar\Blog\Models\Category;
use Lunar\Blog\Models\Tag;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Blog\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => false]);
    $this->staff->givePermissionTo(config('lunar-blog.permission'));
    $this->actingAs($this->staff, 'staff');

    $this->payload = [
        'title' => 'Choosing the right spare',
        'slug' => '',
        'excerpt' => 'A short intro.',
        'author_id' => $this->staff->id,
        'seo_title' => '',
        'seo_description' => '',
        'published_at' => '',
        'categories' => [],
        'tags' => [],
    ];
});

it('creates categories and tags from names and attaches them', function () {
    $this->post(route('panel.blog.articles.store'), [
        ...$this->payload,
        'categories' => ['Bearings', 'Seals'],
        'tags' => ['aico'],
    ]);

    $article = Article::sole();
    expect($article->categories->pluck('name')->all())->toEqualCanonicalizing(['Bearings', 'Seals'])
        ->and($article->tags->pluck('slug')->all())->toBe(['aico'])
        ->and(Category::count())->toBe(2)
        ->and(Tag::count())->toBe(1);
});

it('reuses an existing term by slug instead of duplicating it', function () {
    Category::factory()->create(['name' => 'Bearings', 'slug' => 'bearings']);

    $this->post(route('panel.blog.articles.store'), [
        ...$this->payload,
        // Different case resolves to the same slug.
        'categories' => ['bearings'],
    ]);

    expect(Category::count())->toBe(1)
        ->and(Article::sole()->categories->pluck('slug')->all())->toBe(['bearings']);
});

it('syncs terms on update, dropping removed ones', function () {
    $article = Article::factory()->create(['author_id' => $this->staff->id]);
    $article->categories()->attach(Category::factory()->create(['name' => 'Old', 'slug' => 'old']));

    $this->patch(route('panel.blog.articles.update', $article), [
        ...$this->payload,
        'slug' => $article->slug,
        'categories' => ['New'],
    ]);

    expect($article->fresh()->categories->pluck('slug')->all())->toBe(['new']);
});

it('ignores blank and duplicate term names', function () {
    $this->post(route('panel.blog.articles.store'), [
        ...$this->payload,
        'tags' => ['aico', ' aico ', ''],
    ]);

    expect(Tag::count())->toBe(1)
        ->and(Article::sole()->tags->pluck('slug')->all())->toBe(['aico']);
});
