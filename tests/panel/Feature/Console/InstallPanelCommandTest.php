<?php

use Illuminate\Support\Facades\File;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('publishes the panel config and reports the panel url', function () {
    $configTarget = config_path('lunar/panel.php');
    File::delete($configTarget);

    try {
        $this->artisan('lunar:panel:install')->assertSuccessful();

        expect(File::exists($configTarget))->toBeTrue();
    } finally {
        // vendor:publish writes into the shared testbench skeleton; remove
        // everything the command created so other suites see a clean slate.
        File::delete($configTarget);
        File::deleteDirectory(public_path('vendor/lunar-panel'));
    }
});
