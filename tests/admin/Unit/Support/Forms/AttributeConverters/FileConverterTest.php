<?php

use Filament\Forms\Components\FileUpload;
use Lunar\FieldTypes\File;
use Lunar\Models\Attribute;
use Lunar\Tests\Admin\Unit\Livewire\TestCase;

uses(TestCase::class)
    ->group('livewire.support.forms');

describe('file field converter', function () {
    beforeEach(function () {
        $this->asStaff();
    });

    test('can convert attribute to form input component', function () {
        $attribute = Attribute::factory()->create([
            'type' => File::class,
        ]);

        $inputComponent = Lunar\Admin\Support\FieldTypes\File::getFilamentComponent($attribute);

        expect($inputComponent)->toBeInstanceOf(FileUpload::class);
    });

    test('can configure file upload disk', function () {
        $attribute = Attribute::factory()->create([
            'type' => File::class,
            'configuration' => [
                'disk' => 'public',
            ],
        ]);

        $inputComponent = Lunar\Admin\Support\FieldTypes\File::getFilamentComponent($attribute);

        expect($inputComponent->getDiskName())->toBe('public');
    });

    test('can configure file upload directory', function () {
        $attribute = Attribute::factory()->create([
            'type' => File::class,
            'configuration' => [
                'directory' => 'products/images',
            ],
        ]);

        $inputComponent = Lunar\Admin\Support\FieldTypes\File::getFilamentComponent($attribute);

        expect($inputComponent->getDirectory())->toBe('products/images');
    });

    test('can configure file upload disk and directory together', function () {
        $attribute = Attribute::factory()->create([
            'type' => File::class,
            'configuration' => [
                'disk' => 's3',
                'directory' => 'catalog/files',
            ],
        ]);

        $inputComponent = Lunar\Admin\Support\FieldTypes\File::getFilamentComponent($attribute);

        expect($inputComponent->getDiskName())->toBe('s3')
            ->and($inputComponent->getDirectory())->toBe('catalog/files');
    });
});
