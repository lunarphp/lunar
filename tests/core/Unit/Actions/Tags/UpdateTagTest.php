<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Tags\UpdateTag;
use Lunar\Core\Models\Tag;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('updates the tag attributes', function () {
    $tag = Tag::factory()->create(['value' => 'Summer']);

    app(UpdateTag::class)->execute($tag, ['value' => 'Winter']);

    $this->assertDatabaseHas('lunar_tags', [
        'id' => $tag->id,
        'value' => 'WINTER',
    ]);
});
