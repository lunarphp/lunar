<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Lunar\SearchRelevance\Settings;
use Lunar\Tests\SearchRelevance\TestCase;

uses(TestCase::class)->group('search-relevance');

it('falls back to config when nothing is persisted', function () {
    Config::set('lunar.search_relevance.mode', 'on');

    expect(app(Settings::class)->mode())->toBe('on');
});

it('persists a mode override that wins over config', function () {
    Config::set('lunar.search_relevance.mode', 'shadow');

    app(Settings::class)->setMode('on');

    expect(app(Settings::class)->mode())->toBe('on')
        ->and(DB::table('lunar_search_relevance_settings')->where('key', 'mode')->value('value'))->toBe(json_encode('on'));

    app(Settings::class)->setMode('off');

    expect(app(Settings::class)->mode())->toBe('off');
});

it('memoises the mode for the instance and clears it on write', function () {
    $settings = app(Settings::class);

    expect($settings->mode())->toBe('shadow');

    DB::table('lunar_search_relevance_settings')->insert(['key' => 'mode', 'value' => json_encode('on'), 'created_at' => now(), 'updated_at' => now()]);

    expect($settings->mode())->toBe('shadow');

    $settings->setMode('off');

    expect($settings->mode())->toBe('off');
});

it('rejects unknown modes', function () {
    app(Settings::class)->setMode('turbo');
})->throws(InvalidArgumentException::class);

it('ignores an invalid persisted value', function () {
    DB::table('lunar_search_relevance_settings')->insert(['key' => 'mode', 'value' => json_encode('turbo'), 'created_at' => now(), 'updated_at' => now()]);

    expect(app(Settings::class)->mode())->toBe('shadow');
});

it('falls back to config when the settings table does not exist yet', function () {
    Config::set('lunar.database.table_prefix', 'missing_');

    expect(app(Settings::class)->mode())->toBe('shadow');
});
