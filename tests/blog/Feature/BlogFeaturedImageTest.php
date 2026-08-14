<?php

use Illuminate\Http\UploadedFile;
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

it('attaches an uploaded featured image and stores its alt text', function () {
    $this->post(route('panel.blog.articles.store'), [
        ...$this->payload,
        'featured_image' => UploadedFile::fake()->image('hero.jpg', 20, 20),
        'featured_image_alt' => 'A bearing on a workbench',
    ]);

    $article = Article::sole();
    expect($article->getMedia('featured'))->toHaveCount(1)
        ->and($article->featured_image_alt)->toBe('A bearing on a workbench');
});

it('replaces the previous image on re-upload (single-file collection)', function () {
    $article = Article::factory()->create(['author_id' => $this->staff->id]);
    $article->addMedia(UploadedFile::fake()->image('old.jpg', 10, 10))->toMediaCollection('featured');

    $this->patch(route('panel.blog.articles.update', $article), [
        ...$this->payload,
        'slug' => $article->slug,
        'featured_image' => UploadedFile::fake()->image('new.jpg', 10, 10),
    ]);

    $media = $article->fresh()->getMedia('featured');
    expect($media)->toHaveCount(1)
        ->and($media->first()->file_name)->toBe('new.jpg');
});

it('removes the featured image when asked', function () {
    $article = Article::factory()->create(['author_id' => $this->staff->id]);
    $article->addMedia(UploadedFile::fake()->image('hero.jpg', 10, 10))->toMediaCollection('featured');

    $this->patch(route('panel.blog.articles.update', $article), [
        ...$this->payload,
        'slug' => $article->slug,
        'remove_featured_image' => true,
    ]);

    expect($article->fresh()->getMedia('featured'))->toHaveCount(0);
});

it('uses the configured media collection instead of the hardcoded default', function () {
    config(['lunar-blog.media.collection' => 'hero']);

    $this->post(route('panel.blog.articles.store'), [
        ...$this->payload,
        'featured_image' => UploadedFile::fake()->image('hero.jpg', 20, 20),
    ]);

    $article = Article::sole();
    expect($article->getMedia('hero'))->toHaveCount(1)
        ->and($article->featuredImageUrl())->not->toBeNull();
});

it('rejects a non-image upload', function () {
    $this->post(route('panel.blog.articles.store'), [
        ...$this->payload,
        'featured_image' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
    ])->assertSessionHasErrors('featured_image');

    expect(Article::count())->toBe(0);
});
