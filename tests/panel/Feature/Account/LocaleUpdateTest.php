<?php

use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('persists a preferred locale for the signed-in staff member', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->from('/panel')
        ->put('/panel/account/locale', ['locale' => 'de'])
        ->assertRedirect('/panel');

    expect($staff->fresh()->preferred_locale)->toBe('de');
});

it('rejects a locale the panel does not ship', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->from('/panel')
        ->put('/panel/account/locale', ['locale' => 'xx'])
        ->assertSessionHasErrors('locale');

    expect($staff->fresh()->preferred_locale)->toBeNull();
});

it('redirects guests to the login screen', function () {
    $this->put('/panel/account/locale', ['locale' => 'de'])
        ->assertRedirect(route('panel.login'));
});

it('serves the panel in the preferred locale', function () {
    $staff = Staff::factory()->create(['admin' => true, 'preferred_locale' => 'de']);

    $this->actingAs($staff, 'staff')
        ->get('/panel')
        ->assertInertia(fn ($page) => $page->where('locale', 'de'));

    expect(app()->getLocale())->toBe('de');
});

it('ignores a stored locale the panel no longer ships', function () {
    $staff = Staff::factory()->create(['admin' => true]);
    $staff->forceFill(['preferred_locale' => 'xx'])->save();

    $this->actingAs($staff, 'staff')
        ->get('/panel')
        ->assertInertia(fn ($page) => $page->where('locale', 'en'));
});

it('shares the panel locales as the availableLocales prop', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->get('/panel')
        ->assertInertia(fn ($page) => $page
            ->where('availableLocales', fn ($locales) => collect($locales)
                ->contains('en') && collect($locales)->contains('pt_BR') && count($locales) === 16)
        );
});
