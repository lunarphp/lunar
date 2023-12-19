<?php

uses(\Lunar\Tests\ScoutDatabaseEngine\TestCase::class);

use Illuminate\Support\Facades\Artisan;
use Lunar\ScoutDatabaseEngine\SearchIndex;
use Lunar\Tests\ScoutDatabaseEngine\Stubs\Post;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can index a post', function () {
    $post = new Post();
    $post->title = 'Example Post';
    $post->body = 'Test 1 2 3';
    $post->save();

    expect($post)->toBeInstanceOf(Post::class);

    $this->assertDatabaseCount('search_index', 4);

    $this->assertDatabaseHas('search_index', [
        'key' => $post->getScoutKey(),
        'index' => $post->searchableAs(),
        'field' => 'title',
        'content' => $post->title,
    ]);
});

test('can index a post with nulls', function () {
    $post = new Post();
    $post->title = 'Example Post';
    $post->body = null;
    $post->save();

    expect($post)->toBeInstanceOf(Post::class);

    $this->assertDatabaseCount('search_index', 3);

    $this->assertDatabaseMissing('search_index', [
        'key' => $post->getScoutKey(),
        'index' => $post->searchableAs(),
        'field' => 'body',
        'content' => '',
    ]);
});

test('deletes outdated index data', function () {
    $searchIndex = new SearchIndex();
    $searchIndex->key = '10';
    $searchIndex->index = 'posts';
    $searchIndex->field = 'title';
    $searchIndex->content = 'To be deleted';
    $searchIndex->save();

    $post = new Post();
    $post->id = 10;
    $post->title = 'Example Post';
    $post->body = 'Test 1 2 3';
    $post->save();

    $this->assertDatabaseMissing('search_index', [
        'key' => '10',
        'index' => 'posts',
        'field' => 'title',
        'content' => 'To be deleted',
    ]);
});

test('deletes old index data', function () {
    $post = new Post();
    $post->id = 15;
    $post->title = 'Example Post';
    $post->body = 'Test 1 2 3';
    $post->save();

    $this->assertDatabaseHas('search_index', [
        'key' => '15',
        'index' => 'posts',
        'field' => 'title',
        'content' => 'Example Post',
    ]);

    $post->delete();

    $this->assertDatabaseMissing('search_index', [
        'key' => '15',
        'index' => 'posts',
        'field' => 'title',
        'content' => 'Example Post',
    ]);
});

test('can flush data', function () {
    $post = new Post();
    $post->title = 'Example Post';
    $post->body = 'Test 1 2 3';
    $post->save();

    $post = new Post();
    $post->title = 'Example Post 2';
    $post->body = 'Test 4 5 6';
    $post->save();

    $post = new Post();
    $post->title = 'Example Post 3';
    $post->body = 'Test 7 8 9';
    $post->save();

    // 3 models x 2 fields = 6
    $this->assertDatabaseCount('search_index', 12);

    Artisan::call('scout:flush "Lunar\\\ScoutDatabaseEngine\\\Tests\\\Stubs\\\Post"');

    $this->assertDatabaseCount('search_index', 0);
});
