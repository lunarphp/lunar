<?php

use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Lunar\Admin\Support\Synthesizers\TranslatedTextSynth;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Language;
use Lunar\Tests\Admin\Unit\Livewire\TestCase;
use stdClass;

uses(TestCase::class)
    ->group('support.synthesizers');

it('dehydrates translated text values as plain locale strings', function () {
    Language::factory()->create([
        'code' => 'en',
        'default' => true,
    ]);

    Language::factory()->create([
        'code' => 'es',
        'default' => false,
    ]);

    $field = new TranslatedText(collect([
        'en' => new Text('<p>English description</p>'),
        'es' => new Text('Descripcion'),
    ]));

    $synth = new TranslatedTextSynth(
        new ComponentContext(new stdClass),
        'attribute_data.description',
    );
    [$state] = $synth->dehydrate($field);

    expect($state)->toBe([
        'en' => '<p>English description</p>',
        'es' => 'Descripcion',
    ])->and($synth->get($field, 'en'))->toBe('<p>English description</p>');
});

it('stores rich editor document payloads as html for translated text fields', function () {
    $field = new TranslatedText(collect());

    $synth = new TranslatedTextSynth(
        new ComponentContext(new stdClass),
        'attribute_data.description',
    );
    $synth->set($field, 'en', [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'paragraph',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Rich description',
                    ],
                ],
            ],
        ],
    ]);

    expect($field->getValue()->get('en'))->toBeInstanceOf(Text::class)
        ->and($field->getValue()->get('en')?->getValue())->toBe('<p>Rich description</p>');
});
