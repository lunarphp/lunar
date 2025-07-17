<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Lunar\Admin\Filament\Resources\StaffResource\Pages\AccessControl;
use Lunar\Admin\Filament\Resources\StaffResource\Pages\CreateStaff;
use Lunar\Admin\Filament\Resources\StaffResource\Pages\EditStaff;
use Lunar\Admin\Filament\Resources\StaffResource\Pages\ListStaff;
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
        return TextInput::make('first_name')
            ->label(__('lunarpanel::staff.form.first_name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getLastNameFormComponent(): Component
    {
        return TextInput::make('last_name')
            ->label(__('lunarpanel::staff.form.last_name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('lunarpanel::staff.form.email.label'))
            ->email()
            ->required()
            ->unique(ignoreRecord: true)
            ->maxLength(255);
    }

    protected static function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
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
        return Select::make('roles')
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
            ->afterStateHydrated(fn (Select $component, $record) => $component->state($record?->getRoleNames()->toArray() ?? []))
            ->afterStateUpdated(function ($set, Select $component) {
                $permName = 'permissions';

                /** @var PermissionSelector $permission */
                $permission = collect($component->getContainer()->getFlatComponents())
                    ->first(fn (Field $component) => $component->getName() == $permName);

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
        return Grid::make()
            ->hidden(fn ($record) => $record ? $record->admin : false)
            ->schema([
                static::getRoleFormComponent(),
                static::getPermissionFormComponent(),
            ]);
    }

    protected static function getSuperAdminNotice(): Component
    {
        return Toggle::make('admin')
            ->label(__('lunarpanel::staff.form.admin.label'))
            ->helperText(__('lunarpanel::staff.form.admin.helper'))
            ->visible(fn ($record) => $record ? $record->admin : false)
            ->disabled();
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label(__('lunarpanel::staff.table.first_name.label')),
                TextColumn::make('last_name')
                    ->label(__('lunarpanel::staff.table.last_name.label')),
                TextColumn::make('email')
                    ->label(__('lunarpanel::staff.table.email.label')),
                TextColumn::make('admin')
                    ->label('')
                    ->badge()
                    ->state(function (Model $record): string {
                        return $record->admin ? __('lunarpanel::staff.table.admin.badge') : '';
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListStaff::route('/'),
            'acl' => AccessControl::route('/access-control'),
            'create' => CreateStaff::route('/create'),
            'edit' => EditStaff::route('/{record}/edit'),
        ];
    }
}
