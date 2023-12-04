<?php

uses(\Lunar\ScoutDatabaseEngine\Tests\TestCase::class);
use Lunar\ScoutDatabaseEngine\SearchIndex;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can create model', function () {
    $searchIndex = new SearchIndex();
    $searchIndex->key = 1;
    $searchIndex->index = 'posts';
    $searchIndex->field = 'title';
    $searchIndex->content = 'Test 1 2 3';
    $searchIndex->save();

    expect($searchIndex)->toBeInstanceOf(SearchIndex::class);
});
