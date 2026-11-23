<?php

use Illuminate\Support\Facades\File;
use Lunar\Tests\Core\TestCase;

use function Pest\Laravel\artisan;

/**
 * Publishes into a per-test database path, so the copies never reach the
 * shared Testbench migrations directory other tests migrate from.
 */
class PublishMigrationsTestCase extends TestCase
{
    public string $databasePath;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $this->databasePath = sys_get_temp_dir().'/lunar-publish-migrations-'.uniqid();
        File::ensureDirectoryExists($this->databasePath.'/migrations');

        $app->useDatabasePath($this->databasePath);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->databasePath);

        parent::tearDown();
    }
}

uses(PublishMigrationsTestCase::class);

test('published migrations are dated when they are published, in their original order', function () {
    $source = collect(File::files(__DIR__.'/../../../../packages/core/database/migrations'))
        ->map(fn ($file) => $file->getFilename())
        ->sort()
        ->values();

    $publishedAt = now()->format('Y_m_d_His');

    artisan('vendor:publish', ['--tag' => 'lunar.migrations'])->assertSuccessful();

    $published = collect(File::files($this->databasePath.'/migrations'))
        ->map(fn ($file) => $file->getFilename())
        ->sort()
        ->values();

    $stripDate = fn (string $name): string => substr($name, strlen('2026_01_01_000000_'));

    // Dated after the publish, so the copies run after every migration the
    // application already has, such as its users table.
    expect($published)->toHaveCount($source->count())
        ->and($published->every(fn (string $name): bool => substr($name, 0, 17) > $publishedAt))->toBeTrue()
        ->and($published->map($stripDate)->all())->toBe($source->map($stripDate)->all());
});
