<?php

namespace Lunar\ScoutDatabaseEngine\Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Lunar\ScoutDatabaseEngine\SearchIndex;
use Lunar\ScoutDatabaseEngine\Tests\Stubs\Post;
use Lunar\ScoutDatabaseEngine\Tests\TestCase;

class SearchTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function can_search_a_post()
    {
        $this->seedPosts();

        $posts = Post::search('Lamborghini')->get();

        $this->assertEquals($posts->first()->body, 'Lamborghini');
    }

    /** @test */
    public function can_do_an_empty_search()
    {
        $this->seedPosts();

        $posts = Post::search('')->get();

        $this->assertCount(Post::count(), $posts);
    }

    /** @test */
    public function can_raw_search_a_post()
    {
        $this->seedPosts();

        $results = Post::search('Lamborghini')->raw();

        $this->assertInstanceOf(SearchIndex::class, $results->first());
    }

    /** @test */
    public function can_paginate()
    {
        $this->seedPosts();

        $posts = Post::search('Lamborghini')->paginate();

        $this->assertEquals($posts->first()->body, 'Lamborghini');
    }

    /** @test */
    public function can_specify_index()
    {
        $this->seedPosts();

        $posts = Post::search('Lamborghini')->within('posts')->get();

        $this->assertEquals($posts->first()->body, 'Lamborghini');
    }

    protected function seedPosts()
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
}
