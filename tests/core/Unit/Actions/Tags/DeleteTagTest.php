<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Tags\DeleteTag;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\Tag;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('deletes a tag and untags its records', function () {
    $tag = Tag::factory()->create();
    $product = Product::factory()->create();
    $product->tags()->attach($tag);

    app(DeleteTag::class)->execute($tag);

    $this->assertDatabaseMissing('lunar_tags', ['id' => $tag->id]);
    $this->assertDatabaseMissing('lunar_taggables', ['tag_id' => $tag->id]);
});
