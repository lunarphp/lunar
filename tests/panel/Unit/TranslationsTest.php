<?php

use Illuminate\Translation\MessageSelector;
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

/**
 * The auth and nav guards above predate this one and stay as named checks; this
 * closes the gap for every other group, so a new lang file cannot ship English
 * keys that were never translated.
 */
test('every locale mirrors the english keys of every lang group', function () {
    $dir = dirname(__DIR__, 3).'/packages/panel/resources/lang';

    $groups = collect(glob($dir.'/en/*.php'))
        ->map(fn (string $path) => basename($path, '.php'));

    expect($groups)->not->toBeEmpty();

    foreach ($groups as $group) {
        $base = array_keys(trans("panel::{$group}", [], 'en'));

        foreach (PANEL_LOCALES as $locale) {
            expect(file_exists("{$dir}/{$locale}/{$group}.php"))
                ->toBeTrue("Locale {$locale} is missing the {$group} lang group");

            expect(array_keys(trans("panel::{$group}", [], $locale)))
                ->toBe($base, "Locale {$locale} is missing or has extra {$group} keys");
        }
    }
});

/**
 * Laravel's trans_choice() quietly falls back to the first segment when a
 * locale has fewer forms than its plural rule needs, so a missing form reads
 * as "1 variants" or "5 wariant" rather than failing.
 */
test('every locale carries a form for each plural category of flash_bulk_skipped', function () {
    $selector = new MessageSelector;

    foreach (['en', ...PANEL_LOCALES] as $locale) {
        $forms = count(explode('|', trans('panel::products.flash_bulk_skipped', [], $locale)));

        $needed = collect(range(0, 200))
            ->map(fn (int $count) => $selector->getPluralIndex($locale, $count))
            ->max() + 1;

        expect($forms)->toBe($needed, "Locale {$locale} needs {$needed} plural forms for flash_bulk_skipped");
    }
});
