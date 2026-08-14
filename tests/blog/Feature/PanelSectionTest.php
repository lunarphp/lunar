<?php

use Lunar\Core\Models\Staff;
use Lunar\Tests\Blog\TestCase;

uses(TestCase::class);

it('registers the blog panel index route', function () {
    expect(Route::has('panel.blog.articles.index'))->toBeTrue();
});

it('forbids staff without the blog permission', function () {
    $staff = Staff::factory()->create(['admin' => false]);

    $this->actingAs($staff, 'staff')
        ->get(route('panel.blog.articles.index'))
        ->assertForbidden();
});

it('allows staff with the blog permission to view the index', function () {
    $staff = Staff::factory()->create(['admin' => false]);
    $staff->givePermissionTo(config('lunar-blog.permission'));

    $this->actingAs($staff, 'staff')
        ->get(route('panel.blog.articles.index'))
        ->assertOk();
});

it('hides the blog navigation item from staff without the permission', function () {
    $staff = Staff::factory()->create(['admin' => false]);

    $this->actingAs($staff, 'staff')
        ->get('/panel')
        ->assertInertia(fn ($page) => $page
            ->where('navigation.groups', fn ($groups) => ! collect($groups)->pluck('key')->contains('blog')));
});

it('shows the blog navigation item to staff with the permission', function () {
    $staff = Staff::factory()->create(['admin' => false]);
    $staff->givePermissionTo(config('lunar-blog.permission'));

    $this->actingAs($staff, 'staff')
        ->get('/panel')
        ->assertInertia(fn ($page) => $page
            ->where('navigation.groups', fn ($groups) => collect($groups)->pluck('key')->contains('blog')));
});
