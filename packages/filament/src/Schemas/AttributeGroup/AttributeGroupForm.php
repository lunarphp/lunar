<?php

namespace Lunar\Filament\Schemas\AttributeGroup;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Lunar\Filament\Support\Concerns\CallsHooks;

class AttributeGroupForm
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        return self::callStaticLunarHook(
            'configureForm',
            $schema->components([
                Section::make()->schema(static::getMainComponents()),
            ]),
        );
    }

    public static function getMainComponents(): array
    {
        return [
            static::getNameComponent(),
            static::getHandleComponent(),
            static::getPositionComponent(),
        ];
    }

    public static function getNameComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('lunar-filament::attributegroup.form.name.label'))
            ->required()
            ->maxLength(255)
            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                if ($operation !== 'create') {
                    return;
                }
                $set('handle', Str::slug($state));
            })
            ->live(onBlur: true)
            ->autofocus();
    }

    public static function getHandleComponent(): Component
    {
        return TextInput::make('handle')
            ->label(__('lunar-filament::attributegroup.form.handle.label'))
            ->live(onBlur: true)
            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                if ($operation !== 'create') {
                    return;
                }

                $set('handle', Str::snake(Str::lower($state)));
            })
            ->required()
            ->maxLength(255);
    }

    public static function getPositionComponent(): Component
    {
        return TextInput::make('position')
            ->label(__('lunar-filament::attributegroup.form.position.label'))
            ->numeric()
            ->minValue(1)
            ->maxValue(100)
            ->required();
    }
}
