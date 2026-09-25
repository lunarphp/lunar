<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Log;
use Lunar\Core\Manifests\ModelManifest;
use Lunar\Core\Models\Product;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

/**
 * Records every directory the manifest actually scans, so a test can tell a
 * repeated scan from a remembered one.
 */
function countingModelManifest(): ModelManifest
{
    return new class extends ModelManifest
    {
        /** @var list<string> */
        public array $scanned = [];

        protected function scan(string $dir): array
        {
            $this->scanned[] = $dir;

            return parent::scan($dir);
        }
    };
}

/**
 * @param  array<string, array<class-string>>  $models
 */
function writeModelCache(array $models): void
{
    file_put_contents(
        app()->bootstrapPath('cache/lunar_models.php'),
        '<?php return '.var_export($models, true).';'.PHP_EOL,
    );
}

function coreModelsDirectory(): string
{
    return dirname((new ReflectionClass(Product::class))->getFileName());
}

test('the core models directory is scanned once however many times the manifest asks for it', function (): void {
    $manifest = countingModelManifest();

    $manifest->register();
    $manifest->morphMap();
    $manifest->morphMap();

    expect($manifest->scanned)->toHaveCount(1);
});

test('the morph map still maps every core model', function (): void {
    $manifest = countingModelManifest();

    $manifest->register();
    $manifest->morphMap();

    expect(Relation::getMorphedModel('product'))->toBe(Product::class);
});

test('an added directory is scanned once and registered', function (): void {
    $manifest = countingModelManifest();
    $dir = dirname((new ReflectionClass(Product::class))->getFileName());

    $manifest->addDirectory($dir);
    $manifest->addDirectory($dir);

    expect($manifest->scanned)->toBe([$dir])
        ->and(app('router')->getBindingCallback('product'))->not->toBeNull();
});

test('a missing added directory is logged, not thrown', function (): void {
    $manifest = countingModelManifest();

    Log::shouldReceive('error')->once();

    $manifest->addDirectory(__DIR__.'/does-not-exist');
});

describe('the cache file', function (): void {
    afterEach(function (): void {
        @unlink(app()->bootstrapPath('cache/lunar_models.php'));
    });

    test('a cached directory is read from the file, not scanned', function (): void {
        writeModelCache([coreModelsDirectory() => [Product::class]]);

        $manifest = countingModelManifest();
        $manifest->register();
        $manifest->morphMap();

        expect($manifest->scanned)->toBe([])
            ->and(Relation::getMorphedModel('product'))->toBe(Product::class);
    });

    test('a directory missing from the file falls back to a scan', function (): void {
        writeModelCache(['/somewhere/else' => []]);

        $manifest = countingModelManifest();
        $manifest->register();

        expect($manifest->scanned)->toBe([coreModelsDirectory()]);
    });

    test('building rescans every known directory and ignores a stale file', function (): void {
        writeModelCache([coreModelsDirectory() => [Product::class]]);

        $manifest = countingModelManifest();
        $manifest->register();
        $models = $manifest->cache();

        expect($manifest->scanned)->toBe([coreModelsDirectory()])
            ->and($models[coreModelsDirectory()])->toContain(Product::class)
            ->and(count($models[coreModelsDirectory()]))->toBeGreaterThan(1)
            ->and(require $manifest->cachePath())->toBe($models);
    });

    test('clearing removes the file', function (): void {
        writeModelCache([coreModelsDirectory() => [Product::class]]);

        $manifest = countingModelManifest();
        $manifest->clearCache();

        expect(file_exists($manifest->cachePath()))->toBeFalse();
    });
});
