<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Tags\CreateTag;
use Lunar\Core\Models\Tag;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('creates a tag, upper-casing the value', function () {
    $tag = app(CreateTag::class)->execute(['value' => 'Summer']);

    expect($tag)->toBeInstanceOf(Tag::class);

    $this->assertDatabaseHas('lunar_tags', [
        'id' => $tag->id,
        'value' => 'SUMMER',
    ]);
});
