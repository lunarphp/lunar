<?php

declare(strict_types=1);

use Illuminate\Support\ServiceProvider;
use Lunar\Core\Models\Product;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

afterEach(function (): void {
    @unlink(app()->bootstrapPath('cache/lunar_models.php'));
});

test('lunar:models:cache writes the discovered models and lunar:models:clear removes them', function (): void {
    $path = app()->bootstrapPath('cache/lunar_models.php');

    $this->artisan('lunar:models:cache')->assertSuccessful();

    expect(file_exists($path))->toBeTrue()
        ->and(collect(require $path)->flatten()->all())->toContain(Product::class);

    $this->artisan('lunar:models:clear')->assertSuccessful();

    expect(file_exists($path))->toBeFalse();
});

test('optimize and optimize:clear run the model cache commands', function (): void {
    expect(ServiceProvider::$optimizeCommands)->toHaveKey('lunar-models', 'lunar:models:cache')
        ->and(ServiceProvider::$optimizeClearCommands)->toHaveKey('lunar-models', 'lunar:models:clear');
});
