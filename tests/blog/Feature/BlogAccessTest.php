<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Blog\TestCase;

uses(TestCase::class);

// "Forbids staff without the blog:manage permission" and "hides/shows the nav
// item" are already covered by PanelSectionTest; only the guest redirect and
// the rendered component name are additional here.

it('redirects a guest away from the blog index', function () {
    $this->get(route('panel.blog.articles.index'))
        ->assertRedirect();
});

it('shows the blog index to staff with the blog:manage permission', function () {
    $staff = Staff::factory()->create(['admin' => false]);
    $staff->givePermissionTo(config('lunar-blog.permission'));

    $this->actingAs($staff, 'staff')
        ->get(route('panel.blog.articles.index'))
        ->assertOk()
        // shouldExist: false — the page is registered at runtime via
        // window.LunarPanel, so no page file exists for Inertia to resolve.
        ->assertInertia(fn (Assert $page) => $page
            ->component('blog::Articles/Index', false));
});
