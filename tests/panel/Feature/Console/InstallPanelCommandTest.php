<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Lunar\Panel\PanelServiceProvider;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

/**
 * The install command is asserted through the publish tags it invokes and the
 * mappings the provider registers for them, rather than by running a real
 * vendor:publish.
 *
 * A real publish writes into the Testbench skeleton that every parallel process
 * shares, and the config directory there is globbed and `require`d by Testbench's
 * LoadConfiguration on every app boot. A process that published the file and then
 * cleaned it up left a window in which another process globbed the path, then
 * failed to require it — a bootstrap failure surfacing in whichever unrelated test
 * that process happened to be running.
 */
beforeEach(function () {
    $this->publishCalls = collect();

    // Replaces the framework's command of the same name in the console registry.
    Artisan::command('vendor:publish {--tag=*} {--force} {--provider=} {--all}', function () {
        test()->publishCalls->push([
            'tags' => Arr::wrap($this->option('tag')),
            'force' => (bool) $this->option('force'),
        ]);

        return 0;
    });
});

it('publishes the panel config and reports the panel url', function () {
    $this->artisan('lunar:panel:install')
        ->expectsOutputToContain('Installing the Lunar panel...')
        ->expectsOutputToContain(url(config('lunar.panel.path', 'lunar')))
        ->assertSuccessful();

    expect($this->publishCalls->pluck('tags')->flatten()->all())
        ->toBe(['panel-config', 'panel-all-assets']);
});

it('force-publishes the assets so a stale build is replaced', function () {
    $this->artisan('lunar:panel:install')->assertSuccessful();

    $assets = $this->publishCalls->first(fn (array $call) => in_array('panel-all-assets', $call['tags'], true));

    expect($assets['force'])->toBeTrue();
});

/**
 * ServiceProvider::$publishGroups is static and accumulates for the lifetime of the
 * process, so an add-on registering into the same tag earlier in the run shows up
 * here. These assert the panel's own entries are registered, not that nothing else
 * is — the latter would depend on test ordering.
 */
it('maps the panel-config tag at the published config path', function () {
    $paths = ServiceProvider::pathsToPublish(PanelServiceProvider::class, 'panel-config');

    expect(array_values($paths))->toContain(config_path('lunar/panel.php'));
    expect(collect(array_keys($paths))->contains(
        fn (string $source) => str_ends_with($source, '/config/panel.php')
    ))->toBeTrue();
});

it('maps the panel-all-assets tag at the published asset paths', function () {
    $paths = ServiceProvider::pathsToPublish(PanelServiceProvider::class, 'panel-all-assets');

    expect(array_values($paths))
        ->toContain(public_path('vendor/lunar-panel/build'))
        ->toContain(public_path('vendor/lunar-panel/favicons'));
});

it('leaves the shared Testbench config directory untouched', function () {
    $this->artisan('lunar:panel:install')->assertSuccessful();

    expect(file_exists(config_path('lunar/panel.php')))->toBeFalse();
});
