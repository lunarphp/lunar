<?php

uses(\Lunar\ScoutDatabaseEngine\Tests\TestCase::class);
use Lunar\ScoutDatabaseEngine\SearchIndex;
use Lunar\ScoutDatabaseEngine\Tests\Stubs\Post;

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

test('can search a post', function () {
    seedPosts();

    $posts = Post::search('Lamborghini')->get();

    expect('Lamborghini')->toEqual($posts->first()->body);
});

test('can do an empty search', function () {
    seedPosts();

    $posts = Post::search('')->get();

    expect($posts)->toHaveCount(Post::count());
});

test('can raw search a post', function () {
    seedPosts();

    $results = Post::search('Lamborghini')->raw();

    expect($results->first())->toBeInstanceOf(SearchIndex::class);
});

test('can paginate', function () {
    seedPosts();

    $posts = Post::search('Lamborghini')->paginate();

    expect('Lamborghini')->toEqual($posts->first()->body);
});

test('can specify index', function () {
    seedPosts();

    $posts = Post::search('Lamborghini')->within('posts')->get();

    expect('Lamborghini')->toEqual($posts->first()->body);
});

function seedPosts()
{
    $post = new Post();
    $post->title = 'Supercar';
    $post->body = 'Lamborghini';
    $post->save();

    $post = new Post();
    $post->title = 'Supercar';
    $post->body = 'Ferrari';
    $post->save();

    $post = new Post();
    $post->title = 'Supercar';
    $post->body = 'Aston Martin';
    $post->save();

    $post = new Post();
    $post->title = 'Supercar';
    $post->body = 'Mclaren';
    $post->save();
}
