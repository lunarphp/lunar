<?php

namespace Lunar\Tests\Admin;

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
use Livewire\LivewireServiceProvider;
use Lunar\Admin\LunarPanelProvider;
use Lunar\Core\LunarServiceProvider;
use Lunar\Core\Models\Staff;
use Lunar\Filament\LunarFilamentServiceProvider;
use Lunar\Nestedset\NestedSetServiceProvider;
use Lunar\Tests\Admin\Providers\LunarPanelTestServiceProvider;
use Lunar\Tests\Admin\Stubs\User;
use Lunar\Tests\TestCase as BaseTestCase;
use MallardDuck\LucideIcons\BladeLucideIconsServiceProvider;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time to avoid timestamp errors
        $this->freezeTime();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LunarServiceProvider::class,
            LunarFilamentServiceProvider::class,
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

        parent::getEnvironmentSetUp($app);
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

        $resolvedModel = config('lunar.staff.model', Staff::class);

        if ($resolvedModel !== Staff::class) {
            return $resolvedModel::find($staff->getKey());
        }

        return $staff;
    }
}
