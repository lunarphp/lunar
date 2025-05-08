<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Lunar\Admin\Filament\Resources\StaffResource\Pages;
use Lunar\Admin\Models\Staff;
use Lunar\Admin\Support\Facades\LunarAccessControl;
use Lunar\Admin\Support\Forms\Components\PermissionSelector;
use Lunar\Admin\Support\Resources\BaseResource;

class StaffResource extends BaseResource
{
    protected static ?string $permission = 'settings:manage-staff';

    protected static ?string $model = Staff::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::staff.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::staff.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::staff');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.settings');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static function getMainFormComponents(): array
    {
        return [
            static::getFirstNameFormComponent(),
            static::getLastNameFormComponent(),
            static::getEmailFormComponent(),
            static::getPasswordFormComponent(),
            static::getSuperAdminNotice(),
            static::getRolePermissionContainerFormComponent(),
        ];
    }

    protected static function getFirstNameFormComponent(): Component
    {
        return Forms\Components\TextInput::make('first_name')
            ->label(__('lunarpanel::staff.form.first_name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getLastNameFormComponent(): Component
    {
        return Forms\Components\TextInput::make('last_name')
            ->label(__('lunarpanel::staff.form.last_name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getEmailFormComponent(): Component
    {
        return Forms\Components\TextInput::make('email')
            ->label(__('lunarpanel::staff.form.email.label'))
            ->email()
            ->required()
            ->unique(ignoreRecord: true)
            ->maxLength(255);
    }

    protected static function getPasswordFormComponent(): Component
    {
        return Forms\Components\TextInput::make('password')
            ->label(__('lunarpanel::staff.form.password.label'))
            ->password()
            ->required(fn ($record) => blank($record))
            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
            ->dehydrated(fn (?string $state): bool => filled($state))
            ->hint(fn ($record) => filled($record) ? __('lunarpanel::staff.form.password.hint') : null)
            ->maxLength(255);
    }

    protected static function getRoleFormComponent(): Component
    {
        return Forms\Components\Select::make('roles')
            ->label(__('lunarpanel::staff.form.roles.label'))
            ->multiple(true)
            ->options(fn () => LunarAccessControl::getRoles()
                ->when(
                    ! Filament::auth()->user()->hasRole(LunarAccessControl::getAdmin()->toArray()),
                    fn ($roles) => $roles->reject(fn ($r) => LunarAccessControl::getAdmin()->contains($r->handle))
                )
                ->map(fn ($r) => ['handle' => $r->handle, 'label' => $r->transLabel])
                ->pluck('label', 'handle')
                ->toArray())
            ->helperText(function ($state) {
                $inter = LunarAccessControl::getAdmin()->intersect($state);

                if ($count = $inter->count()) {
                    $roles = LunarAccessControl::getRoles()
                        ->map(fn ($r) => ['handle' => $r->handle, 'label' => $r->transLabel])
                        ->pluck('label', 'handle');

                    return trans_choice('lunarpanel::staff.form.roles.helper', $count, ['roles' => $inter->map(fn ($r) => $roles[$r] ?? $r)->join(', ')]);
                }
            })
            ->afterStateHydrated(fn (Forms\Components\Select $component, $record) => $component->state($record?->getRoleNames()->toArray() ?? []))
            ->afterStateUpdated(function ($set, Forms\Components\Select $component) {
                $permName = 'permissions';

                /** @var PermissionSelector $permission */
                $permission = collect($component->getContainer()->getFlatComponents())
                    ->first(fn (Forms\Components\Field $component) => $component->getName() == $permName);

                $set($permName, $permission->getPermissionState());
            })
            ->reactive()
            ->saveRelationshipsUsing(fn ($state, $record) => $record->syncRoles($state))
            ->dehydrated(false);
    }

    protected static function getPermissionFormComponent(): Component
    {
        return PermissionSelector::make('permissions')
            ->label(__('lunarpanel::staff.form.permissions.label'));
    }

    protected static function getRolePermissionContainerFormComponent(): Component
    {
        return Forms\Components\Grid::make()
            ->hidden(fn ($record) => $record ? $record->admin : false)
            ->schema([
                static::getRoleFormComponent(),
                static::getPermissionFormComponent(),
            ]);
    }

    protected static function getSuperAdminNotice(): Component
    {
        return Forms\Components\Toggle::make('admin')
            ->label(__('lunarpanel::staff.form.admin.label'))
            ->helperText(__('lunarpanel::staff.form.admin.helper'))
            ->visible(fn ($record) => $record ? $record->admin : false)
            ->disabled();
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label(__('lunarpanel::staff.table.first_name.label')),
                Tables\Columns\TextColumn::make('last_name')
                    ->label(__('lunarpanel::staff.table.last_name.label')),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('lunarpanel::staff.table.email.label')),
                Tables\Columns\TextColumn::make('admin')
                    ->label('')
                    ->badge()
                    ->state(function (Model $record): string {
                        return $record->admin ? __('lunarpanel::staff.table.admin.badge') : '';
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getDefaultRelations(): array
    {
        return [
            //
        ];
    }

    public static function getDefaultPages(): array
    {
        return [
            'index' => Pages\ListStaff::route('/'),
            'acl' => Pages\AccessControl::route('/access-control'),
            'create' => Pages\CreateStaff::route('/create'),
            'edit' => Pages\EditStaff::route('/{record}/edit'),
        ];
    }
}
