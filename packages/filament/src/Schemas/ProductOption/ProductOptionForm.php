<?php

namespace Lunar\Filament\Schemas\ProductOption;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Lunar\Admin\Support\Concerns\CallsHooks;
use Lunar\Core\Models\Language;
use Lunar\Filament\Forms\Components\TranslatedText;

class ProductOptionForm
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        return self::callStaticLunarHook(
            'configureForm',
            $schema->components([
                Section::make()->schema(static::getMainComponents()),
            ])->columns(1),
        );
    }

    public static function getMainComponents(): array
    {
        return [
            static::getNameComponent(),
            static::getLabelComponent(),
            static::getHandleComponent(),
        ];
    }

    public static function getNameComponent(): Component
    {
        return TranslatedText::make('name')
            ->label(__('lunarpanel::productoption.form.name.label'))
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

    public static function getLabelComponent(): Component
    {
        return TranslatedText::make('label')
            ->label(__('lunarpanel::productoption.form.label.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public static function getHandleComponent(): Component
    {
        return TextInput::make('handle')
            ->label(__('lunarpanel::productoption.form.handle.label'))
            ->required()
            ->maxLength(255)
            ->live(onBlur: true)
            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                if ($operation !== 'create') {
                    return;
                }

                $set('handle', Str::snake(Str::lower($state)));
            })
            ->disabled(fn ($operation, $record) => $operation == 'edit' && (! $record->shared));
    }
}
