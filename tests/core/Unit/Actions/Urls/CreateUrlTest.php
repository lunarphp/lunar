<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Urls\CreateUrl;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Language;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->language = Language::factory()->create(['default' => true]);
    $this->brand = Brand::factory()->create();
});

test('the first url is always the default', function () {
    $url = app(CreateUrl::class)->execute($this->brand, [
        'language_id' => $this->language->id,
        'slug' => 'stark',
        'default' => false,
    ]);

    expect($url->default)->toBeTrue();
});

test('creating a default demotes the current default in the same language', function () {
    $first = app(CreateUrl::class)->execute($this->brand, [
        'language_id' => $this->language->id,
        'slug' => 'stark',
    ]);

    $second = app(CreateUrl::class)->execute($this->brand, [
        'language_id' => $this->language->id,
        'slug' => 'stark-industries',
        'default' => true,
    ]);

    expect($second->default)->toBeTrue()
        ->and($first->refresh()->default)->toBeFalse();
});

test('a non-default create keeps the existing default', function () {
    $first = app(CreateUrl::class)->execute($this->brand, [
        'language_id' => $this->language->id,
        'slug' => 'stark',
    ]);

    $second = app(CreateUrl::class)->execute($this->brand, [
        'language_id' => $this->language->id,
        'slug' => 'stark-industries',
    ]);

    expect($second->default)->toBeFalse()
        ->and($first->refresh()->default)->toBeTrue();
});

test('each language keeps its own default', function () {
    $english = app(CreateUrl::class)->execute($this->brand, [
        'language_id' => $this->language->id,
        'slug' => 'stark',
    ]);

    // The first German URL is German's default, whatever was asked, and
    // leaves the English default alone.
    $german = app(CreateUrl::class)->execute($this->brand, [
        'language_id' => Language::factory()->create(['code' => 'de'])->id,
        'slug' => 'stark-de',
        'default' => false,
    ]);

    expect($german->default)->toBeTrue()
        ->and($english->refresh()->default)->toBeTrue();
});
