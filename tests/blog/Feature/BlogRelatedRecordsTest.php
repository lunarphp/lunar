<?php

use Lunar\Blog\Models\Article;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
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

it('forbids the search endpoint without the blog:manage permission', function () {
    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff')
        ->getJson(route('panel.blog.articles.search', ['type' => 'article', 'q' => 'x']))
        ->assertForbidden();
});

it('returns an empty result set for a blank query', function () {
    $this->getJson(route('panel.blog.articles.search', ['type' => 'product', 'q' => '']))
        ->assertOk()
        ->assertExactJson(['results' => []]);
});

it('finds products by SKU', function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);

    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'sku' => 'BRG-45210']);

    $this->getJson(route('panel.blog.articles.search', ['type' => 'product', 'q' => substr($variant->sku, 0, 4)]))
        ->assertOk()
        ->assertJsonPath('results.0.id', $product->id)
        ->assertJsonPath('results.0.meta', $variant->sku);
});

it('finds articles by title and excludes the given article', function () {
    $target = Article::factory()->create(['title' => 'Bearing maintenance guide']);
    $self = Article::factory()->create(['title' => 'Bearing basics']);

    $results = $this->getJson(route('panel.blog.articles.search', ['type' => 'article', 'q' => 'Bearing', 'exclude' => $self->id]))
        ->assertOk()
        ->json('results');

    $ids = array_column($results, 'id');
    expect($ids)->toContain($target->id)
        ->and($ids)->not->toContain($self->id);
});

it('syncs related products and articles on store', function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);

    $product = Product::factory()->create();
    $related = Article::factory()->create();

    $this->post(route('panel.blog.articles.store'), [
        ...$this->payload,
        'related_products' => [$product->id],
        'related_articles' => [$related->id],
    ]);

    $article = Article::whereKeyNot($related->id)->sole();
    expect($article->relatedProducts->pluck('id')->all())->toBe([$product->id])
        ->and($article->relatedArticles->pluck('id')->all())->toBe([$related->id]);
});

it('never relates an article to itself', function () {
    $article = Article::factory()->create(['author_id' => $this->staff->id]);
    $other = Article::factory()->create();

    $this->patch(route('panel.blog.articles.update', $article), [
        ...$this->payload,
        'slug' => $article->slug,
        'related_articles' => [$article->id, $other->id],
    ])->assertSessionHasNoErrors();

    expect($article->fresh()->relatedArticles->pluck('id')->all())->toBe([$other->id]);
});

it('rejects a related product that does not exist', function () {
    $this->post(route('panel.blog.articles.store'), [
        ...$this->payload,
        'related_products' => [999999],
    ])->assertSessionHasErrors('related_products.0');
});
