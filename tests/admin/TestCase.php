<?php

namespace Lunar\Tests\Admin;

use Awcodes\Shout\ShoutServiceProvider;
use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Kalnoy\Nestedset\NestedSetServiceProvider;
use Livewire\LivewireServiceProvider;
use Lunar\Admin\LunarPanelProvider;
use Lunar\Admin\Models\Staff;
use Lunar\LunarServiceProvider;
use Lunar\Tests\Admin\Providers\LunarPanelTestServiceProvider;
use Lunar\Tests\Admin\Stubs\User;
use Lunar\Tests\TestCase as BaseTestCase;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use Technikermathe\LucideIcons\BladeLucideIconsServiceProvider;

use function Orchestra\Testbench\after_resolving;
use function Orchestra\Testbench\default_migration_path;

class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time to avoid timestamp errors
        $this->freezeTime();
    }

    // Register laravel migrations on the migrator instead of running them
    // separately — a standalone migrate commits DDL and resets RefreshDatabase's
    // per-process cache.
    protected function defineDatabaseMigrations(): void
    {
        after_resolving($this->app, 'migrator', static function ($migrator) {
            $migrator->path(default_migration_path());
        });
    }

    protected function getPackageProviders($app): array
    {
        return [
            LunarServiceProvider::class,
            LunarPanelProvider::class,

            ActionsServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            NotificationsServiceProvider::class,
            SchemasServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            BladeLucideIconsServiceProvider::class,
            ShoutServiceProvider::class,

            LunarPanelTestServiceProvider::class,

            LivewireServiceProvider::class,
            MediaLibraryServiceProvider::class,
            PermissionServiceProvider::class,
            ActivitylogServiceProvider::class,
            NestedSetServiceProvider::class,
            BlinkServiceProvider::class,

        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('auth.passwords.users.table', 'password_reset_tokens');
        $app['config']->set('auth.providers.users.model', User::class);

        $this->replaceModelsForTesting();

        // File-backed SQLite per worker; Testbench wipes RefreshDatabase's
        // cache for `:memory:`, forcing a full migrate every test.
        $dbPath = sys_get_temp_dir().'/lunar-test-'.getmypid().'.sqlite';
        if (! file_exists($dbPath)) {
            touch($dbPath);
        }

        $app['config']->set('database.connections.testing.database', $dbPath);
    }

    protected function asStaff($admin = true): TestCase
    {
        return $this->actingAs($this->makeStaff($admin), 'staff');
    }

    protected function makeStaff($admin = true): Staff
    {
        $staff = Staff::factory()->create([
            'admin' => $admin,
        ]);

        $staff->assignRole($admin ? 'admin' : 'staff');

        return $staff;
    }
}
