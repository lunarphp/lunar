<?php

namespace Lunar\Filament\FieldTypes;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Component;
use Lunar\Core\Models\Attribute;
use Lunar\Filament\Synthesizers\FileSynth;

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
            ->rules($attribute->validation_rules ?? [])
            ->required((bool) $attribute->required)
            ->helperText(null);

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
}
