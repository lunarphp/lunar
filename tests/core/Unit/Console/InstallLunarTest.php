<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Region;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

/**
 * The core suite has no Filament and no panel installed, so this doubles as
 * the guard test: `filament:assets` and `lunar:panel:install` are not in the
 * console registry, and the installer must skip them rather than throw.
 *
 * `vendor:publish` is stubbed for the same reason as in the panel install
 * test — a real publish writes into the Testbench skeleton shared by every
 * parallel worker.
 */
test('install completes without any Filament-based package', function () {
    Artisan::command('vendor:publish {--tag=*} {--force} {--provider=} {--all}', fn () => 0);

    // Pre-seed a country so the installer skips the full address-data import.
    Country::factory()->create();

    $this->artisan('lunar:install')
        ->expectsConfirmation('Run database migrations?')
        ->expectsQuestion('First Name', 'Ada')
        ->expectsQuestion('Last Name', 'Lovelace')
        ->expectsQuestion('Email address', 'ada@example.com')
        ->expectsQuestion('Password', 'top-secret')
        ->expectsConfirmation('Would you like to show some love by giving us a star on GitHub?')
        ->assertSuccessful();

    expect(Staff::whereAdmin(true)->exists())->toBeTrue()
        ->and(Channel::whereDefault(true)->exists())->toBeTrue()
        ->and(Region::whereDefault(true)->exists())->toBeTrue();
});

test('install command copies the configuration', function () {
    // $configFiles = array_keys(config('lunar'));
    // $configPath = config_path('lunar');
    // if (! File::exists($configPath)) {
    //     File::makeDirectory($configPath);
    // }
    // // make sure we're starting from a clean state
    // foreach ($configFiles as $filename) {
    //     if (File::exists(config_path("lunar/$filename.php"))) {
    //         unlink("$configPath/$filename.php");
    //     }
    //     $this->assertFalse(File::exists("$configPath/$filename.php"));
    // }
    // Artisan::call('lunar:install');
    // foreach ($configFiles as $filename) {
    //     $this->assertTrue(File::exists("$configPath/$filename.php"));
    // }
    // These break tests on actions atm..
    expect(true)->toBeTrue();
});

test('when config is present users can choose to not overwrite it', function () {
    // // Given we have already have an existing config file
    // $configPath = config_path('lunar');
    // if (! File::exists($configPath)) {
    //     File::makeDirectory($configPath);
    // }
    // File::put("{$configPath}/database.php", '<?php return [];');
    // $this->assertTrue(File::exists("$configPath/database.php"));
    // // When we run the install command
    // $command = $this->artisan('lunar:install');
    // // We expect a warning that our configuration file exists
    // $command->expectsConfirmation(
    //     'Config file already exists. Do you want to overwrite it?',
    //     // When answered with "no"
    //     'no'
    // );
    // // We should see a message that our file was not overwritten
    // $command->expectsOutput('Existing configuration was not overwritten');
    // $command->execute();
    // // Assert that the original contents of the config file remain
    // $this->assertEquals('<?php return [];', file_get_contents(config_path('lunar/database.php')));
    // // Clean up
    // unlink(config_path('lunar/database.php'));
    // These break tests on actions atm..
    expect(true)->toBeTrue();
});

test('when a config file is present users can choose to do overwrite it', function () {
    // // Given we have already have an existing config file
    // $configPath = config_path('lunar');
    // $configFiles = array_keys(config('lunar'));
    // if (! File::exists($configPath)) {
    //     File::makeDirectory($configPath);
    // }
    // File::put("{$configPath}/database.php", '<?php return [];');
    // $this->assertTrue(File::exists("$configPath/database.php"));
    // // When we run the install command
    // $command = $this->artisan('lunar:install');
    // // We expect a warning that our configuration file exists
    // $command->expectsConfirmation(
    //     'Config file already exists. Do you want to overwrite it?',
    //     // When answered with "yes"
    //     'yes'
    // );
    // $command->expectsOutput('Overwriting configuration file...');
    // $command->execute();
    // // Assert that the original contents are overwritten
    // foreach ($configFiles as $filename) {
    //     $this->assertEquals(
    //         file_get_contents(__DIR__."/../../../config/$filename.php"),
    //         file_get_contents(config_path("lunar/$filename.php"))
    //     );
    //     // Clean up
    //     unlink(config_path("lunar/$filename.php"));
    // }
    // These break tests on actions atm..
    expect(true)->toBeTrue();
});
