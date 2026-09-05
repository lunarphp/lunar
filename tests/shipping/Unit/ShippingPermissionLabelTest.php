<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Support\DataTransferObjects\Permission;
use Lunar\Tests\Shipping\TestCase;

uses(TestCase::class)->group('shipping');

uses(RefreshDatabase::class);

/**
 * The permission is registered by the shipping package's own migration, but
 * label resolution is centralised on `lunar::auth.permissions.{handle}.*` and
 * falls back to the raw handle — so an unlabelled permission rendered as
 * "shipping:manage" in the staff and roles UI of both panels.
 */
it('labels the shipping permission rather than falling back to the handle', function () {
    $permission = Permission::make('shipping:manage', firstParty: true);

    expect($permission->transLabel())->toBe('Manage Shipping')
        ->and($permission->transDescription())->not->toBe('shipping:manage');
});

it('translates the shipping permission label', function (string $locale, string $label) {
    app()->setLocale($locale);

    expect(Permission::make('shipping:manage', firstParty: true)->transLabel())
        ->toBe($label);
})->with([
    'de' => ['de', 'Versand verwalten'],
    'fr' => ['fr', "Gérer l'expédition"],
    'nl' => ['nl', 'Beheer Verzending'],
]);
