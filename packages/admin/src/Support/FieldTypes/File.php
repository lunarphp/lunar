<?php

namespace Lunar\Admin\Support\FieldTypes;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Lunar\Admin\Support\Synthesizers\FileSynth;
use Lunar\Models\Attribute;

class File extends BaseFieldType
{
    protected static string $synthesizer = FileSynth::class;

    public static function getFilamentComponent(Attribute $attribute): Component
    {
        $file_types = $attribute->configuration->get('file_types');
        $multiple = (bool) $attribute->configuration->get('multiple');
        $min_files = $attribute->configuration->get('min_files');
        $max_files = $attribute->configuration->get('max_files');
        $disk = $attribute->configuration->get('disk');
        $directory = $attribute->configuration->get('directory');

        $input = FileUpload::make($attribute->handle)
            ->when(filled($attribute->validation_rules), fn (FileUpload $component) => $component->rules($attribute->validation_rules))
            ->required((bool) $attribute->required)
            ->helperText($attribute->translate('description'));

        if (! blank($file_types) && is_array($file_types)) {
            $input->acceptedFileTypes($file_types);
        }

        if ($multiple) {
            $input->multiple();
        }

        if ($min_files) {
            $input->minFiles($min_files);
        }

        if ($max_files) {
            $input->maxFiles($max_files);
        }

        if ($disk) {
            $input->disk($disk);
        }

        if ($directory) {
            $input->directory($directory);
        }

        return $input;
    }

    public static function getConfigurationFields(): array
    {
        $disks = array_keys(config('filesystems.disks', []));

        return [
            TagsInput::make('file_types')
                ->label(
                    __('lunarpanel::fieldtypes.file.form.file_types.label')
                )->suggestions([
                    'image/*',
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'audio/*',
                    'audio/mpeg',
                    'audio/aac',
                    'audio/wav',
                    'video/*',
                    'video/mp4',
                    'video/mpeg',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/rtf',
                    'application/pdf',
                ])
                ->placeholder(__('lunarpanel::fieldtypes.file.form.file_types.placeholder'))
                ->reorderable(),
            Toggle::make('multiple')->label(
                __('lunarpanel::fieldtypes.file.form.multiple.label')
            ),
            TextInput::make('min_files')
                ->label(
                    __('lunarpanel::fieldtypes.file.form.min_files.label')
                )->nullable()->numeric(),
            TextInput::make('max_files')->label(
                __('lunarpanel::fieldtypes.file.form.max_files.label')
            )->nullable()->numeric(),
            Select::make('disk')
                ->label(__('lunarpanel::fieldtypes.file.form.disk.label'))
                ->options(! empty($disks) ? array_combine($disks, $disks) : [])
                ->nullable(),
            TextInput::make('directory')
                ->label(__('lunarpanel::fieldtypes.file.form.directory.label'))
                ->nullable(),
        ];
    }
}
