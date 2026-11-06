<?php

use Lunar\Tests\Shipping\TestCase;

uses(TestCase::class)->group('shipping', 'shipping-locale-parity');

test('every locale exposes the same driver options in shippingmethod translations', function () {
    $langPath = dirname(__DIR__, 4).'/packages/table-rate-shipping/resources/lang';

    $files = glob($langPath.'/*/shippingmethod.php');

    expect($files)->not->toBeEmpty();

    $referenceLocale = null;
    $referenceFormOptions = null;
    $referenceTableOptions = null;

    foreach ($files as $file) {
        $locale = basename(dirname($file));
        $translations = require $file;

        $formOptions = array_keys($translations['form']['driver']['options'] ?? []);
        $tableOptions = array_keys($translations['table']['driver']['options'] ?? []);

        sort($formOptions);
        sort($tableOptions);

        if ($referenceLocale === null) {
            $referenceLocale = $locale;
            $referenceFormOptions = $formOptions;
            $referenceTableOptions = $tableOptions;

            continue;
        }

        expect($formOptions)
            ->toBe($referenceFormOptions, "Locale [{$locale}] form.driver.options keys do not match [{$referenceLocale}].");

        expect($tableOptions)
            ->toBe($referenceTableOptions, "Locale [{$locale}] table.driver.options keys do not match [{$referenceLocale}].");
    }
});

test('every locale includes the flat-rate and free-shipping driver options', function () {
    $langPath = dirname(__DIR__, 4).'/packages/table-rate-shipping/resources/lang';

    $files = glob($langPath.'/*/shippingmethod.php');

    foreach ($files as $file) {
        $locale = basename(dirname($file));
        $translations = require $file;

        expect($translations['form']['driver']['options'] ?? [])
            ->toHaveKeys(['flat-rate', 'free-shipping'], "Locale [{$locale}] is missing form.driver.options entries.");

        expect($translations['table']['driver']['options'] ?? [])
            ->toHaveKeys(['flat-rate', 'free-shipping'], "Locale [{$locale}] is missing table.driver.options entries.");
    }
});
