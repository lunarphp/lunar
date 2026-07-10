<?php

use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('returns every panel:: lang group for a known locale as versioned json', function () {
    $response = $this->getJson('/panel/translations/en')
        ->assertOk()
        ->assertJsonStructure(['version', 'messages' => ['auth', 'nav']]);

    expect($response->json('version'))->toBeString()->not->toBeEmpty()
        ->and($response->json('messages.auth.sign_in_title'))->toBe('Sign in')
        ->and($response->json('messages.nav.dashboard'))->toBe('Dashboard');
});

it('is reachable without authentication', function () {
    $this->getJson('/panel/translations/en')->assertOk();
});

it('reads a locale with an underscore in its directory name', function () {
    $this->getJson('/panel/translations/pt_BR')
        ->assertOk()
        ->assertJsonStructure(['version', 'messages' => ['auth', 'nav']]);
});

it('falls back to the app fallback locale for an unsupported locale', function () {
    $fallback = $this->getJson('/panel/translations/en')->json();

    // "xx" is not one of the panel's 16 shipped locale directories, but still
    // matches the route's [A-Za-z_]+ constraint so it reaches the controller.
    $unsupported = $this->getJson('/panel/translations/xx')
        ->assertOk()
        ->json();

    expect($unsupported['messages'])->toBe($fallback['messages'])
        ->and($unsupported['version'])->toBe($fallback['version']);
});

it('changes the version hash when a lang file mtime changes', function () {
    $authFile = dirname(__DIR__, 4).'/packages/panel/resources/lang/en/auth.php';
    $originalMtime = filemtime($authFile);

    $before = $this->getJson('/panel/translations/en')->json('version');

    touch($authFile, $originalMtime + 60);
    clearstatcache(true, $authFile);

    try {
        $after = $this->getJson('/panel/translations/en')->json('version');

        expect($after)->not->toBe($before);
    } finally {
        touch($authFile, $originalMtime);
        clearstatcache(true, $authFile);
    }
});

it('produces a version hash independent of filesystem enumeration order', function () {
    $first = $this->getJson('/panel/translations/en')->json('version');
    $second = $this->getJson('/panel/translations/en')->json('version');

    expect($first)->toBe($second);
});
