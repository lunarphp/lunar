<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Urls\UpdateUrl;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Language;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    $language = Language::factory()->create(['default' => true]);
    $this->brand = Brand::factory()->create();

    $this->default = $this->brand->urls()->create([
        'language_id' => $language->id,
        'slug' => 'stark',
        'default' => true,
    ]);

    $this->other = $this->brand->urls()->create([
        'language_id' => Language::factory()->create(['code' => 'de'])->id,
        'slug' => 'stark-de',
        'default' => false,
    ]);
});

test('updates the slug', function () {
    app(UpdateUrl::class)->execute($this->other, ['slug' => 'stark-germany']);

    expect($this->other->refresh()->slug)->toBe('stark-germany');
});

test('promoting a url to default demotes its siblings', function () {
    app(UpdateUrl::class)->execute($this->other, ['default' => true]);

    expect($this->other->refresh()->default)->toBeTrue()
        ->and($this->default->refresh()->default)->toBeFalse();
});

test('the default cannot be unset directly', function () {
    app(UpdateUrl::class)->execute($this->default, ['default' => false, 'slug' => 'renamed']);

    expect($this->default->refresh()->default)->toBeTrue()
        ->and($this->default->slug)->toBe('renamed');
});
