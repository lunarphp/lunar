<?php

namespace Lunar\Filament\Schemas\Activity;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Lunar\Filament\Support\Concerns\CallsHooks;

class ActivityForm
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        return self::callStaticLunarHook(
            'configureForm',
            $schema->components([
                TextInput::make('causer_type')
                    ->label(__('lunar-filament::activity.form.causer_type'))
                    ->columnSpan(['default' => 2, 'md' => 1]),
                TextInput::make('causer_id')
                    ->label(__('lunar-filament::activity.form.causer_id'))
                    ->columnSpan(['default' => 2, 'md' => 1]),
                TextInput::make('subject_type')
                    ->label(__('lunar-filament::activity.form.subject_type'))
                    ->columnSpan(['default' => 2, 'md' => 1]),
                TextInput::make('subject_id')
                    ->label(__('lunar-filament::activity.form.subject_id'))
                    ->columnSpan(['default' => 2, 'md' => 1]),
                TextInput::make('description')
                    ->label(__('lunar-filament::activity.form.description'))
                    ->columnSpan(2),
                KeyValue::make('properties.attributes')
                    ->label(__('lunar-filament::activity.form.attributes'))
                    ->columnSpan(['default' => 2, 'md' => 1]),
                KeyValue::make('properties.old')
                    ->label(__('lunar-filament::activity.form.old'))
                    ->columnSpan(['default' => 2, 'md' => 1]),
            ]),
        );
    }
}
