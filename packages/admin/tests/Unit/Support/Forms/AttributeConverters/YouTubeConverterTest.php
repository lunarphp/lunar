
<?php

uses(\Lunar\Admin\Tests\Unit\Livewire\TestCase::class)
    ->group('livewire.support.forms');

describe('youtube field converter', function () {
    beforeEach(function () {
        $this->asStaff();
    });

    test('can convert attribute to form input component', function () {
        $attribute = \Lunar\Models\Attribute::factory()->create([
            'type' => \Lunar\FieldTypes\YouTube::class,
        ]);

        $inputComponent = \Lunar\Admin\Support\FieldTypes\YouTube::getFilamentComponent($attribute);

        expect($inputComponent)->toBeInstanceOf(\Lunar\Admin\Support\Forms\Components\YouTube::class);
    });
});
