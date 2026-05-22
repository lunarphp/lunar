<?php

namespace Lunar\Filament\Schemas\AttributeGroup;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Lunar\Admin\Support\Concerns\CallsHooks;
use Lunar\Filament\Forms\Components\TranslatedText;
use Lunar\Core\Facades\AttributeManifest;
use Lunar\Core\Facades\ModelManifest;
use Lunar\Core\Models\Language;

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
            static::getAttributableTypeComponent(),
            static::getNameComponent(),
            static::getHandleComponent(),
            static::getPositionComponent(),
        ];
    }

    public static function getAttributableTypeComponent(): Component
    {
        return Select::make('attributable_type')
            ->label(__('lunarpanel::attributegroup.form.attributable_type.label'))
            ->options(function () {
                return AttributeManifest::getTypes()->mapWithKeys(
                    fn ($type) => [
                        ModelManifest::getMorphMapKey($type) => class_basename($type),
                    ]
                );
            })
            ->required()
            ->autofocus();
    }

    public static function getNameComponent(): Component
    {
        return TranslatedText::make('name')
            ->label(__('lunarpanel::attributegroup.form.name.label'))
            ->required()
            ->maxLength(255)
            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                if ($operation !== 'create') {
                    return;
                }
                $set('handle', Str::slug($state[Language::getDefault()->code]));
            })
            ->live(onBlur: true)
            ->autofocus();
    }

    public static function getHandleComponent(): Component
    {
        return TextInput::make('handle')
            ->label(__('lunarpanel::attributegroup.form.handle.label'))
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
            ->label(__('lunarpanel::attributegroup.form.position.label'))
            ->numeric()
            ->minValue(1)
            ->maxValue(100)
            ->required();
    }
}
