<?php

namespace Lunar\Filament\Schemas\Staff;

use Filament\Facades\Filament;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Lunar\Filament\Forms\Components\PermissionSelector;
use Lunar\Filament\Support\Concerns\CallsHooks;

class StaffForm
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        return self::callStaticLunarHook(
            'configureForm',
            $schema->components(static::getMainComponents()),
        );
    }

    public static function getMainComponents(): array
    {
        return [
            static::getFirstNameComponent(),
            static::getLastNameComponent(),
            static::getEmailComponent(),
            static::getPasswordComponent(),
            static::getSuperAdminNotice(),
            static::getRolePermissionContainerComponent(),
        ];
    }

    public static function getFirstNameComponent(): Component
    {
        return TextInput::make('first_name')
            ->label(__('lunar-filament::staff.form.first_name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getLastNameComponent(): Component
    {
        return TextInput::make('last_name')
            ->label(__('lunar-filament::staff.form.last_name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getEmailComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('lunar-filament::staff.form.email.label'))
            ->email()
            ->required()
            ->unique(ignoreRecord: true)
            ->maxLength(255);
    }

    public static function getPasswordComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('lunar-filament::staff.form.password.label'))
            ->password()
            ->required(fn ($record) => blank($record))
            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
            ->dehydrated(fn (?string $state): bool => filled($state))
            ->hint(fn ($record) => filled($record) ? __('lunar-filament::staff.form.password.hint') : null)
            ->maxLength(255);
    }

    public static function getRoleComponent(): Component
    {
        return Select::make('roles')
            ->label(__('lunar-filament::staff.form.roles.label'))
            ->multiple(true)
            ->options(fn () => app('lunar-access-control')->getRoles()
                ->when(
                    ! Filament::auth()->user()->hasRole(app('lunar-access-control')->getAdmin()->toArray()),
                    fn ($roles) => $roles->reject(fn ($r) => app('lunar-access-control')->getAdmin()->contains($r->handle))
                )
                ->map(fn ($r) => ['handle' => $r->handle, 'label' => $r->transLabel])
                ->pluck('label', 'handle')
                ->toArray())
            ->helperText(function ($state) {
                $inter = app('lunar-access-control')->getAdmin()->intersect($state);

                if ($count = $inter->count()) {
                    $roles = app('lunar-access-control')->getRoles()
                        ->map(fn ($r) => ['handle' => $r->handle, 'label' => $r->transLabel])
                        ->pluck('label', 'handle');

                    return trans_choice('lunar-filament::staff.form.roles.helper', $count, ['roles' => $inter->map(fn ($r) => $roles[$r] ?? $r)->join(', ')]);
                }
            })
            ->afterStateHydrated(fn (Select $component, $record) => $component->state($record?->getRoleNames()->toArray() ?? []))
            ->afterStateUpdated(function ($set, Select $component) {
                $permName = 'permissions';

                /** @var PermissionSelector $permission */
                $permission = collect($component->getContainer()->getFlatComponents())
                    ->first(fn (Field $component) => $component->getName() == $permName);

                $set($permName, $permission->getPermissionState());
            })
            ->live()
            ->saveRelationshipsUsing(fn ($state, $record) => $record->syncRoles($state))
            ->dehydrated(false);
    }

    public static function getPermissionComponent(): Component
    {
        return PermissionSelector::make('permissions')
            ->label(__('lunar-filament::staff.form.permissions.label'));
    }

    public static function getRolePermissionContainerComponent(): Component
    {
        return Grid::make()
            ->hidden(fn ($record) => $record ? $record->admin : false)
            ->schema([
                static::getRoleComponent(),
                static::getPermissionComponent(),
            ]);
    }

    public static function getSuperAdminNotice(): Component
    {
        return Toggle::make('admin')
            ->label(__('lunar-filament::staff.form.admin.label'))
            ->helperText(__('lunar-filament::staff.form.admin.helper'))
            ->visible(fn ($record) => $record ? $record->admin : false)
            ->disabled();
    }
}
