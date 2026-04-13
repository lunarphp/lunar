<?php

use Lunar\Utils\MeasurementConverter;

beforeEach(function () {
    $this->converter = new MeasurementConverter([
        'length' => [
            'm' => ['format' => '1,0.000 m', 'unit' => 1.00],
            'mm' => ['format' => '1,0.000 mm', 'unit' => 1000],
            'cm' => ['format' => '1!0 cm', 'unit' => 100],
            'ft' => ['format' => '1,0.00 ft.', 'unit' => 3.28084],
            'in' => ['format' => '1,0.00 in.', 'unit' => 39.3701],
        ],
        'weight' => [
            'kg' => ['format' => '1,0.00 kg', 'unit' => 1.00],
            'g' => ['format' => '1,0.00 g', 'unit' => 1000.00],
            'lbs' => ['format' => '1,0.00 lbs', 'unit' => 2.20462],
        ],
        'volume' => [
            'l' => ['format' => '1,00.00l', 'unit' => 1],
            'ml' => ['format' => '1,00.000ml', 'unit' => 1000],
        ],
    ]);
});

test('can convert mm to cm', function () {
    $result = $this->converter
        ->from('length.mm')
        ->value(100)
        ->to('length.cm')
        ->convert()
        ->getValue();

    expect($result)->toBe(10.0);
});

test('can convert cm to mm', function () {
    $result = $this->converter
        ->from('length.cm')
        ->value(10)
        ->to('length.mm')
        ->convert()
        ->getValue();

    expect($result)->toBe(100.0);
});

test('can convert kg to g', function () {
    $result = $this->converter
        ->from('weight.kg')
        ->value(2.5)
        ->to('weight.g')
        ->convert()
        ->getValue();

    expect($result)->toBe(2500.0);
});

test('can convert ml to l', function () {
    $result = $this->converter
        ->from('volume.ml')
        ->value(1500)
        ->to('volume.l')
        ->convert()
        ->getValue();

    expect($result)->toBe(1.5);
});

test('from returns a new instance', function () {
    $instance = $this->converter->from('length.mm');

    expect($instance)->not->toBe($this->converter);
    expect($instance)->toBeInstanceOf(MeasurementConverter::class);
});

test('value and from can be called in either order', function () {
    $result1 = $this->converter
        ->from('length.mm')
        ->value(100)
        ->to('length.cm')
        ->convert()
        ->getValue();

    $result2 = $this->converter
        ->value(100)
        ->from('length.mm')
        ->to('length.cm')
        ->convert()
        ->getValue();

    expect($result1)->toBe($result2);
});

test('throws for unknown measurement', function () {
    $this->converter
        ->from('length.unknown')
        ->value(100)
        ->to('length.cm')
        ->convert();
})->throws(InvalidArgumentException::class, 'Unknown measurement: length.unknown');

test('can get and set measurements', function () {
    $converter = new MeasurementConverter;
    expect($converter->getMeasurements())->toBe([]);

    $measurements = ['length' => ['m' => ['unit' => 1]]];
    $converter->setMeasurements($measurements);
    expect($converter->getMeasurements())->toBe($measurements);
});

test('can chain from value to convert for volume calculation', function () {
    // Simulates the volume calculation in ManageVariantShipping:
    // Convert 100mm x 50mm x 25mm to cm, then multiply, then convert ml to l
    $length = $this->converter->value(100)->from('length.mm')->to('length.cm')->convert()->getValue();
    $width = $this->converter->value(50)->from('length.mm')->to('length.cm')->convert()->getValue();
    $height = $this->converter->value(25)->from('length.mm')->to('length.cm')->convert()->getValue();

    $volumeInMl = $length * $width * $height; // 10 * 5 * 2.5 = 125
    $volumeInL = $this->converter->from('volume.ml')->to('volume.l')->value($volumeInMl)->convert()->getValue();

    expect($length)->toBe(10.0);
    expect($width)->toBe(5.0);
    expect($height)->toBe(2.5);
    expect($volumeInL)->toBe(0.125);
});

test('can format a value using the from measurement format', function () {
    $result = $this->converter
        ->from('length.cm')
        ->value(50);

    expect($result->format())->toBe('50 cm');
});

test('can format a value using the to measurement format', function () {
    $result = $this->converter
        ->from('length.mm')
        ->value(500)
        ->to('length.cm')
        ->convert();

    expect($result->format())->toBe('50 cm');
});

test('can format with thousands separator', function () {
    $result = $this->converter
        ->from('length.mm')
        ->value(1500000);

    expect($result->format())->toBe('1,500,000.000 mm');
});

test('can format with custom format string', function () {
    $result = $this->converter
        ->from('length.cm')
        ->value(1050.5);

    expect($result->format('1,0.00 centimeters'))->toBe('1,050.50 centimeters');
});

test('can format weight values', function () {
    $result = $this->converter
        ->from('weight.kg')
        ->value(2.5);

    expect($result->format())->toBe('2.50 kg');
});

test('can format negative values', function () {
    $result = $this->converter
        ->from('weight.kg')
        ->value(-2.5);

    expect($result->format())->toBe('-2.50 kg');
});

test('format returns plain value when no format string available', function () {
    $converter = new MeasurementConverter([
        'length' => ['x' => ['unit' => 1]],
    ]);

    $result = $converter->from('length.x')->value(42);

    expect($result->format())->toBe('42');
});
