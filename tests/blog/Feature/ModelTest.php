<?php

use Lunar\Blog\Models\Article;
use Lunar\Blog\Models\Category;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Blog\TestCase;
use Spatie\Permission\Models\Permission;

uses(TestCase::class);

it('runs the package migrations and creates the blog tables', function () {
    expect(Schema::hasTable('blog_articles'))->toBeTrue()
        ->and(Schema::hasTable('blog_categories'))->toBeTrue()
        ->and(Schema::hasTable('blog_article_related_product'))->toBeTrue();
});

it('seeds the configured blog permission', function () {
    expect(Permission::where('name', config('lunar-blog.permission'))->exists())->toBeTrue();
});

it('published scope excludes drafts, future and soft-deleted', function () {
    $live = Article::factory()->create(['published_at' => now()->subDay()]);
    Article::factory()->draft()->create();
    Article::factory()->create(['published_at' => now()->addDay()]);
    $deleted = Article::factory()->create(['published_at' => now()->subDay()]);
    $deleted->delete();

    expect(Article::published()->pluck('id')->all())->toBe([$live->id]);
});

it('derives author name and body excerpt', function () {
    $article = Article::factory()->create([
        'body' => ['type' => 'doc', 'content' => [
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Hello world']]],
        ]],
    ]);

    expect($article->bodyExcerpt())->toBe('Hello world');
});

it('attaches categories', function () {
    $article = Article::factory()->create();
    $article->categories()->attach(Category::factory()->create(['name' => 'Pumps', 'slug' => 'pumps']));

    expect($article->categories->first()->name)->toBe('Pumps');
});

it('derives the author name from the attached staff member', function () {
    $author = Staff::factory()->create(['first_name' => 'First', 'last_name' => 'Last']);
    $article = Article::factory()->create(['author_id' => $author->id]);

    expect($article->authorName())->toBe('First Last');
});
