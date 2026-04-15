
<?php

use Lunar\FieldTypes\YouTube;
use Lunar\Models\Attribute;
use Lunar\Tests\Admin\Unit\Livewire\TestCase;

uses(TestCase::class)
    ->group('livewire.support.forms');

describe('youtube field converter', function () {
    beforeEach(function () {
        $this->asStaff();
    });

    test('can convert attribute to form input component', function () {
        $attribute = Attribute::factory()->create([
            'type' => YouTube::class,
        ]);

        $inputComponent = Lunar\Admin\Support\FieldTypes\YouTube::getFilamentComponent($attribute);

        expect($inputComponent)->toBeInstanceOf(Lunar\Admin\Support\Forms\Components\YouTube::class);
    });
});
