<?php

namespace Lunar\Tests\Admin;

use Awcodes\Shout\ShoutServiceProvider;
use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
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
use Marvinosswald\FilamentInputSelectAffix\FilamentInputSelectAffixServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use Technikermathe\LucideIcons\BladeLucideIconsServiceProvider;

class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();

        // Freeze time to avoid timestamp errors
        $this->freezeTime();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LunarServiceProvider::class,
            LunarPanelProvider::class,

            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            NotificationsServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            BladeLucideIconsServiceProvider::class,
            FilamentInputSelectAffixServiceProvider::class,
            ShoutServiceProvider::class,

            LunarPanelTestServiceProvider::class,

            LivewireServiceProvider::class,
            MediaLibraryServiceProvider::class,
            PermissionServiceProvider::class,
            ActivitylogServiceProvider::class,
            ConverterServiceProvider::class,
            NestedSetServiceProvider::class,
            BlinkServiceProvider::class,

        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('auth.passwords.users.table', 'password_reset_tokens');
        $app['config']->set('auth.providers.users.model', User::class);
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
