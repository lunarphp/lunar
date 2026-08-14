<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Blog\TestCase;

uses(TestCase::class);

// Deliberately does not override `lunar-blog.author_model`, unlike the other
// blog tests, so this exercises the shipped default end to end.
//
// Note on what this can and cannot catch in this monorepo: the real bug was
// the default pointing at `Lunar\Filament\Models\Staff`, a class this package
// does not depend on. `Lunar\Filament\Models\Staff` extends
// `Lunar\Core\Models\Staff` and shares its table, so in a repo where every
// package's autoloader is present the controller round trip below would
// still succeed even with the old, wrong default — the class-not-found
// failure only shows up in a real install of just `lunarphp/panel` +
// `lunarphp/core`. The explicit config assertion is what actually pins the
// default to the panel's own staff model rather than the Filament one.
it('renders the create form with authors from the default author model', function () {
    expect(config('lunar-blog.author_model'))->toBe(Staff::class);

    $staff = Staff::factory()->create(['admin' => false]);
    $staff->givePermissionTo(config('lunar-blog.permission'));

    $this->actingAs($staff, 'staff')
        ->get(route('panel.blog.articles.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('blog::Articles/Edit', false)
            ->has('authors', 1)
            ->where('authors.0.id', $staff->id));
});
