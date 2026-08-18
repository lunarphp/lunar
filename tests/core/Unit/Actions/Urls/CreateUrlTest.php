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

test('creating a default demotes the current default', function () {
    $first = app(CreateUrl::class)->execute($this->brand, [
        'language_id' => $this->language->id,
        'slug' => 'stark',
    ]);

    $second = app(CreateUrl::class)->execute($this->brand, [
        'language_id' => Language::factory()->create(['code' => 'de'])->id,
        'slug' => 'stark-de',
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
        'language_id' => Language::factory()->create(['code' => 'de'])->id,
        'slug' => 'stark-de',
    ]);

    expect($second->default)->toBeFalse()
        ->and($first->refresh()->default)->toBeTrue();
});
