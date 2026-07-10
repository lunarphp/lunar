<?php

use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

const PANEL_NAV_LOCALES = ['ar', 'bg', 'de', 'es', 'fa', 'fr', 'hr', 'hu', 'mn', 'nl', 'pl', 'pt_BR', 'ro', 'tr', 'vi'];

test('every locale mirrors the english nav keys', function () {
    $base = array_keys(trans('panel::nav', [], 'en'));

    foreach (PANEL_NAV_LOCALES as $locale) {
        expect(array_keys(trans('panel::nav', [], $locale)))
            ->toBe($base, "Locale {$locale} is missing or has extra nav keys");
    }
});
