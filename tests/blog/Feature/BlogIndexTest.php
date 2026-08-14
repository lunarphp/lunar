<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Blog\Models\Article;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Blog\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => false]);
    $this->staff->givePermissionTo(config('lunar-blog.permission'));
    $this->actingAs($this->staff, 'staff');

    // Pin a UK trading timezone with a known BST offset so the
    // human-readable publish date assertion below is deterministic.
    config(['lunar-blog.publish_timezone' => 'Europe/London']);
});

it('filters the list by a title search', function () {
    Article::factory()->create(['title' => 'Bearing maintenance']);
    Article::factory()->create(['title' => 'Valve replacement']);

    $this->get(route('panel.blog.articles.index', ['q' => 'bearing']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('blog::Articles/Index', false)
            ->has('articles.data', 1)
            ->where('articles.data.0.title', 'Bearing maintenance'));
});

it('filters by published status', function () {
    Article::factory()->create(['title' => 'Live one']);
    Article::factory()->draft()->create(['title' => 'Draft one']);

    $this->get(route('panel.blog.articles.index', ['status' => 'draft']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('articles.data', 1)
            ->where('articles.data.0.title', 'Draft one')
            ->where('articles.data.0.status', 'draft'));
});

it('renders the publish date in a human-readable trading-time format', function () {
    // Stored UTC 23:00 on 10 Aug is 00:00 on 11 Aug in BST (+1).
    Article::factory()->create([
        'title' => 'Timed',
        'published_at' => '2026-08-10T23:00:00Z',
    ]);

    $this->get(route('panel.blog.articles.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('articles.data.0.published', '11 Aug 2026, 00:00'));
});

it('exposes edit and delete action urls per row', function () {
    $article = Article::factory()->create();

    $this->get(route('panel.blog.articles.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('articles.data.0._actions.edit', route('panel.blog.articles.edit', $article))
            ->where('articles.data.0._actions.delete', route('panel.blog.articles.destroy', $article)));
});
