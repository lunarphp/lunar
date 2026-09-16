<?php

use Illuminate\Validation\Rule;
use Lunar\Api\OpenApi\RulesToSchema;
use Lunar\Core\Enums\SellingPolicy;

test('types, formats, nullability and required come from the rules', function (): void {
    $schema = RulesToSchema::convert([
        'name' => ['required', 'string', 'max:255'],
        'email' => 'required|email',
        'website' => ['nullable', 'url'],
        'quantity' => ['sometimes', 'integer', 'between:1,10'],
        'weight' => ['numeric', 'min:0.5'],
        'accepted' => ['accepted'],
        'starts_at' => ['nullable', 'date', 'after:now'],
        'ref' => ['uuid'],
        'anything' => ['sometimes'],
    ], ['name' => 'The display name.'])->toArray();

    expect($schema['type'])->toBe('object');
    expect($schema['required'])->toBe(['name', 'email']);
    expect($schema['properties']['name'])->toBe(['type' => 'string', 'maxLength' => 255, 'description' => 'The display name.']);
    expect($schema['properties']['email'])->toBe(['type' => 'string', 'format' => 'email']);
    expect($schema['properties']['website'])->toBe(['type' => ['string', 'null'], 'format' => 'uri']);
    expect($schema['properties']['quantity'])->toBe(['type' => 'integer', 'minimum' => 1, 'maximum' => 10]);
    expect($schema['properties']['weight'])->toBe(['type' => 'number', 'minimum' => 0.5]);
    expect($schema['properties']['accepted'])->toBe(['type' => 'boolean']);
    expect($schema['properties']['starts_at'])->toBe(['type' => ['string', 'null'], 'format' => 'date-time']);
    expect($schema['properties']['ref'])->toBe(['type' => 'string', 'format' => 'uuid']);
    expect($schema['properties']['anything'])->toBe([]);
});

test('arrays, nested objects and enums map to their JSON Schema shapes', function (): void {
    $schema = RulesToSchema::convert([
        'abilities' => ['required', 'array', 'min:1'],
        'abilities.*' => ['string', Rule::in(['catalog:read', 'sales:read'])],
        'policy' => [Rule::enum(SellingPolicy::class)],
        'status' => ['in:draft,published'],
        'address' => ['array'],
        'address.line_one' => ['required', 'string'],
        'address.postcode' => ['nullable', 'string', 'size:8'],
        'tags' => ['list'],
        'staff_id' => ['sometimes', 'nullable', 'string', Rule::exists('users', 'id')],
    ], ['abilities.*' => 'A permission handle.'])->toArray();

    expect($schema['properties']['abilities'])->toBe([
        'type' => 'array',
        'items' => ['type' => 'string', 'enum' => ['catalog:read', 'sales:read'], 'description' => 'A permission handle.'],
        'minItems' => 1,
    ]);
    expect($schema['properties']['policy'])->toBe(['type' => 'string', 'enum' => ['always', 'in_stock', 'in_stock_or_on_backorder']]);
    expect($schema['properties']['status'])->toBe(['type' => 'string', 'enum' => ['draft', 'published']]);
    expect($schema['properties']['address'])->toBe([
        'type' => 'object',
        'properties' => [
            'line_one' => ['type' => 'string'],
            'postcode' => ['type' => ['string', 'null'], 'minLength' => 8, 'maxLength' => 8],
        ],
        'required' => ['line_one'],
    ]);
    expect($schema['properties']['tags'])->toBe(['type' => 'array', 'items' => []]);
    expect($schema['properties']['staff_id'])->toBe(['type' => ['string', 'null']]);
    expect($schema['required'])->toBe(['abilities']);
});
