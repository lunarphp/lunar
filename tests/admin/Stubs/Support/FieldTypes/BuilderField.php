<?php

namespace Lunar\Tests\Admin\Stubs\Support\FieldTypes;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Lunar\Admin\Support\FieldTypes\BaseFieldType;

class BuilderField extends BaseFieldType
{
    protected static string $synthesizer = \Lunar\Tests\Admin\Stubs\Support\Synthesizers\BuilderSynth::class;

    public static function getFilamentComponent(\Lunar\Models\Attribute $attribute): Component
    {
        return Repeater::make($attribute->handle)
            ->schema([
                TextInput::make('type')->required(),
                TextInput::make('data.content'),
                TextInput::make('data.author'),
            ])
            ->default([])
            ->collapsed()
            ->formatStateUsing(function ($state) {
                if (! is_array($state)) {
                    return [];
                }

                $normalized = [];
                foreach ($state as $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $type = $item['type'] ?? null;
                    $data = isset($item['data']) && is_array($item['data']) ? $item['data'] : [];

                    if (! is_null($type)) {
                        $normalized[] = [
                            'type' => $type,
                            'data' => $data,
                        ];
                    }
                }

                return array_values($normalized);
            })
            ->mutateDehydratedStateUsing(function ($state) {
                if (! is_array($state)) {
                    return [];
                }

                $normalized = [];
                foreach ($state as $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $type = $item['type'] ?? null;
                    $data = isset($item['data']) && is_array($item['data']) ? $item['data'] : [];
                    // Strip null/empty values from data
                    $data = array_filter($data, fn ($v) => ! is_null($v) && $v !== '');

                    if (! is_null($type)) {
                        $normalized[] = [
                            'type' => $type,
                            'data' => $data,
                        ];
                    }
                }

                return array_values($normalized);
            });
    }
}
