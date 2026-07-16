<?php

use Lunar\Panel\Facades\Panel;
use Lunar\Tests\Panel\Fixtures\AddonTestCase;

uses(AddonTestCase::class);

it('registers section lang namespaces on the manager', function () {
    expect(Panel::translationNamespaces())->toContain('widgets-addon');
});

it('serves opted-in add-on lang groups as namespaced message keys', function () {
    $this->getJson('/panel/translations/en')
        ->assertOk()
        ->assertJsonPath('messages.widgets-addon::widgets.title', 'Widgets')
        ->assertJsonPath('messages.widgets-addon::widgets.greeting', 'Hello from the widgets add-on');
});

it('serves the add-on locale when the add-on ships it', function () {
    $this->getJson('/panel/translations/de')
        ->assertOk()
        ->assertJsonPath('messages.widgets-addon::widgets.title', 'Widgets (DE)');
});

it('falls back per namespace when the add-on lacks the requested locale', function () {
    // The panel ships fr; the fixture add-on ships only en and de. The panel
    // groups must stay French while the add-on group falls back to English.
    $this->getJson('/panel/translations/fr')
        ->assertOk()
        ->assertJsonPath('messages.nav.language', 'Langue')
        ->assertJsonPath('messages.widgets-addon::widgets.title', 'Widgets');
});

it('does not serve namespaces that have not opted in', function () {
    app('translator')->addNamespace('other-addon', dirname(__DIR__, 2).'/Fixtures/resources/lang');

    $messages = $this->getJson('/panel/translations/en')->json('messages');

    expect(array_keys($messages))
        ->toContain('widgets-addon::widgets')
        ->not->toContain('other-addon::widgets');
});

it('includes add-on lang files in the version hash', function () {
    $addonFile = dirname(__DIR__, 2).'/Fixtures/resources/lang/de/widgets.php';
    $originalMtime = filemtime($addonFile);

    $before = $this->getJson('/panel/translations/de')->json('version');

    touch($addonFile, $originalMtime + 60);
    clearstatcache(true, $addonFile);

    try {
        $after = $this->getJson('/panel/translations/de')->json('version');

        expect($after)->not->toBe($before);
    } finally {
        touch($addonFile, $originalMtime);
        clearstatcache(true, $addonFile);
    }
});
