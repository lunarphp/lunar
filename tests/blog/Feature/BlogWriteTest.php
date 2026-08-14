<?php

use Lunar\Blog\Models\Article;
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

it('creates an article and derives the slug from the title', function () {
    $this->post(route('panel.blog.articles.store'), $this->payload)
        ->assertRedirect();

    $article = Article::sole();
    expect($article->title)->toBe('Choosing the right spare')
        ->and($article->slug)->toBe('choosing-the-right-spare')
        ->and($article->author_id)->toBe($this->staff->id);
});

it('stores the tiptap body as a structured document', function () {
    $body = [
        'type' => 'doc',
        'content' => [
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Hello spares.']]],
        ],
    ];

    $this->post(route('panel.blog.articles.store'), [...$this->payload, 'body' => $body]);

    expect(Article::sole()->body)->toBe($body);
});

it('leaves the body null when none is given', function () {
    $this->post(route('panel.blog.articles.store'), $this->payload);

    expect(Article::sole()->body)->toBeNull();
});

it('keeps a draft when no publish date is given', function () {
    $this->post(route('panel.blog.articles.store'), [...$this->payload, 'published_at' => '']);

    expect(Article::sole()->published_at)->toBeNull();
});

it('converts the publish time from trading timezone to UTC', function () {
    config(['lunar-blog.publish_timezone' => 'Europe/London']);

    // 1 June is BST (+1), so 12:00 UK wall-clock is 11:00 UTC.
    $this->post(route('panel.blog.articles.store'), [...$this->payload, 'published_at' => '2026-06-01T12:00']);

    expect(Article::sole()->published_at->utc()->format('Y-m-d H:i'))->toBe('2026-06-01 11:00');
});

it('rejects a duplicate slug', function () {
    Article::factory()->create(['slug' => 'taken']);

    $this->post(route('panel.blog.articles.store'), [...$this->payload, 'slug' => 'taken'])
        ->assertSessionHasErrors('slug');

    expect(Article::count())->toBe(1);
});

it('lets an article keep its own slug on update', function () {
    $article = Article::factory()->create(['slug' => 'keep-me', 'author_id' => $this->staff->id]);

    $this->patch(route('panel.blog.articles.update', $article), [
        ...$this->payload,
        'slug' => 'keep-me',
        'title' => 'Updated title',
    ])->assertSessionHasNoErrors();

    expect($article->fresh()->title)->toBe('Updated title');
});

it('soft-deletes an article', function () {
    $article = Article::factory()->create();

    $this->delete(route('panel.blog.articles.destroy', $article))
        ->assertRedirect(route('panel.blog.articles.index'));

    expect(Article::count())->toBe(0)
        ->and(Article::withTrashed()->count())->toBe(1);
});

it('forbids writes without the blog:manage permission', function () {
    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff')
        ->post(route('panel.blog.articles.store'), $this->payload)
        ->assertForbidden();
});
