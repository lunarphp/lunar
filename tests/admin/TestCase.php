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
use Kalnoy\Nestedset\NestedSetServiceProvider;
use Livewire\LivewireServiceProvider;
use Lunar\Admin\LunarPanelProvider;
use Lunar\Admin\Models\Staff;
use Lunar\Tests\Admin\Providers\LunarPanelTestServiceProvider;
use Lunar\Tests\Admin\Stubs\User;
use Lunar\Tests\LunarTestCase;
use Marvinosswald\FilamentInputSelectAffix\FilamentInputSelectAffixServiceProvider;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use Technikermathe\LucideIcons\BladeLucideIconsServiceProvider;

class TestCase extends LunarTestCase
{
    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
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
        ]);
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('auth.passwords.users.table', 'password_reset_tokens');
        $app['config']->set('auth.providers.users.model', User::class);
    }

    protected function asStaff($admin = true): TestCase
    {
        return $this->actingAs(
            user: $this->makeStaff($admin),
            guard: 'staff',
        );
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
