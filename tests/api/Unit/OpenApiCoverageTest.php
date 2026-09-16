<?php

use Lunar\Api\Contracts\ApiManager;
use Lunar\Api\OpenApi\TypeInference;
use Lunar\Api\Resources\Translations;
use Lunar\Tests\Api\TestCase;

uses(TestCase::class);

/**
 * Every built-in field and filter must be typed, so the shipped documents
 * are never degraded by an x-lunar-untyped schema.
 */
test('every built-in field and filter on every surface is typed', function (): void {
    $untyped = [];

    foreach (app(ApiManager::class)->surfaces() as $key => $registry) {
        foreach ($registry->definitions() as $definition) {
            foreach ($definition->fields() as $field) {
                if (TypeInference::field($field, $definition->model(), Translations::Map)->isUntyped()) {
                    $untyped[] = "{$key} {$definition->type()}.{$field->name}";
                }
            }

            foreach ($definition->filters() as $filter) {
                if (TypeInference::filter($filter, $definition->model())->isUntyped()) {
                    $untyped[] = "{$key} {$definition->type()} filter[{$filter->name}]";
                }
            }
        }
    }

    expect($untyped)->toBe([]);
});

test('every built-in field, include, filter and sort is described', function (): void {
    $missing = [];

    foreach (app(ApiManager::class)->surfaces() as $key => $registry) {
        foreach ($registry->definitions() as $definition) {
            if ($definition->resource::description() === '') {
                $missing[] = "{$key} {$definition->type()}";
            }

            foreach (['fields', 'includes', 'filters', 'sorts'] as $group) {
                foreach ($definition->{$group}() as $member) {
                    if ($member->description() === null) {
                        $missing[] = "{$key} {$definition->type()} {$group}.{$member->name}";
                    }
                }
            }
        }
    }

    expect($missing)->toBe([]);
});
