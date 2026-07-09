<?php

use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

const PANEL_LOCALES = ['ar', 'bg', 'de', 'es', 'fa', 'fr', 'hr', 'hu', 'mn', 'nl', 'pl', 'pt_BR', 'ro', 'tr', 'vi'];

test('the auth lang group exists', function () {
    expect(trans('panel::auth', [], 'en'))->toBeArray()->not->toBeEmpty();
});

test('every locale mirrors the english auth keys', function () {
    $base = array_keys(trans('panel::auth', [], 'en'));

    foreach (PANEL_LOCALES as $locale) {
        expect(array_keys(trans('panel::auth', [], $locale)))
            ->toBe($base, "Locale {$locale} is missing or has extra auth keys");
    }
});
