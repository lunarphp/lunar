<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Tests\Upgrade\TestCase;

uses(TestCase::class);

const SPEC0019_PREFIX = 'upg0019_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => SPEC0019_PREFIX]);
});

afterEach(function () {
    foreach ([
        'products',
        'product_variants',
        'brands',
        'collections',
        'customers',
        'customer_groups',
        'product_types',
        'attributables',
        'product_type_attribute',
        'attribute_models',
        'attributes',
        'attribute_groups',
    ] as $table) {
        Schema::dropIfExists(SPEC0019_PREFIX.$table);
    }
});

function attributeReshapeMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*convert_attribute_data_keys_to_ids.php');

    return require $path[0];
}

function simulateV1AttributeShape(): void
{
    Schema::create(SPEC0019_PREFIX.'product_types', function (Blueprint $table) {
        $table->id();
        $table->string('name');
    });

    Schema::create(SPEC0019_PREFIX.'attribute_groups', function (Blueprint $table) {
        $table->id();
        $table->string('attributable_type');
        $table->json('name');
        $table->string('handle');
        $table->integer('position');
        $table->timestamps();
    });

    Schema::create(SPEC0019_PREFIX.'attributes', function (Blueprint $table) {
        $table->id();
        $table->string('attribute_type');
        $table->foreignId('attribute_group_id');
        $table->integer('position');
        $table->json('name');
        $table->string('handle');
        $table->string('section')->nullable();
        $table->string('type');
        $table->boolean('required')->default(false);
        $table->json('default_value')->nullable();
        $table->string('validation_rules')->nullable();
        $table->json('configuration')->nullable();
        $table->boolean('system')->default(false);
        $table->boolean('searchable')->default(true);
        $table->boolean('filterable')->default(false);
        $table->timestamps();
    });

    Schema::create(SPEC0019_PREFIX.'attributables', function (Blueprint $table) {
        $table->id();
        $table->string('attributable_type');
        $table->foreignId('attributable_id');
        $table->foreignId('attribute_id');
        $table->timestamps();
    });

    Schema::create(SPEC0019_PREFIX.'products', function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_type_id');
        $table->json('attribute_data')->nullable();
        $table->timestamps();
    });

    Schema::create(SPEC0019_PREFIX.'product_variants', function (Blueprint $table) {
        $table->id();
        $table->json('attribute_data')->nullable();
    });

    Schema::create(SPEC0019_PREFIX.'brands', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->json('attribute_data')->nullable();
    });

    DB::table(SPEC0019_PREFIX.'product_types')->insert(['id' => 1, 'name' => 'Stock']);

    DB::table(SPEC0019_PREFIX.'attribute_groups')->insert([
        'id' => 1,
        'attributable_type' => 'product',
        'name' => json_encode(['en' => 'Details']),
        'handle' => 'details',
        'position' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table(SPEC0019_PREFIX.'attributes')->insert([
        [
            'id' => 10,
            'attribute_type' => 'product',
            'attribute_group_id' => 1,
            'position' => 1,
            'name' => json_encode(['en' => 'Meta Title']),
            'handle' => 'meta_title',
            'type' => 'Lunar\\Core\\FieldTypes\\Text',
            'validation_rules' => 'min:1|max:10| ',
            'configuration' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 11,
            'attribute_type' => 'product',
            'attribute_group_id' => 1,
            'position' => 2,
            'name' => json_encode(['en' => 'Featured']),
            'handle' => 'featured',
            'type' => 'Lunar\\Core\\FieldTypes\\Toggle',
            'validation_rules' => null,
            'configuration' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 20,
            'attribute_type' => 'Lunar\\Core\\Models\\Brand',
            'attribute_group_id' => 1,
            'position' => 1,
            'name' => json_encode(['en' => 'Tagline']),
            'handle' => 'tagline',
            'type' => 'Lunar\\Core\\FieldTypes\\Text',
            'validation_rules' => '',
            'configuration' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    DB::table(SPEC0019_PREFIX.'attributables')->insert([
        [
            'attributable_type' => 'product_type',
            'attributable_id' => 1,
            'attribute_id' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'attributable_type' => 'Lunar\\Core\\Models\\ProductType',
            'attributable_id' => 1,
            'attribute_id' => 11,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    DB::table(SPEC0019_PREFIX.'products')->insert([
        'id' => 1,
        'product_type_id' => 1,
        'attribute_data' => json_encode([
            'meta_title' => [
                'field_type' => 'Lunar\\Core\\FieldTypes\\Text',
                'value' => 'Trainers',
            ],
            'featured' => [
                'field_type' => 'Lunar\\Core\\FieldTypes\\Toggle',
                'value' => true,
            ],
            'orphaned' => [
                'field_type' => 'Lunar\\Core\\FieldTypes\\Text',
                'value' => 'gone',
            ],
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table(SPEC0019_PREFIX.'brands')->insert([
        'id' => 1,
        'name' => 'Acme',
        'attribute_data' => json_encode([
            'tagline' => [
                'field_type' => 'Lunar\\Core\\FieldTypes\\Text',
                'value' => 'Boom',
            ],
        ]),
    ]);
}

test('it rewrites attribute_data to id-keyed raw values', function () {
    simulateV1AttributeShape();

    attributeReshapeMigration()->up();

    $product = DB::table(SPEC0019_PREFIX.'products')->find(1);
    expect(json_decode($product->attribute_data, true))->toBe([
        '10' => 'Trainers',
        '11' => true,
    ]);

    $brand = DB::table(SPEC0019_PREFIX.'brands')->find(1);
    expect(json_decode($brand->attribute_data, true))->toBe(['20' => 'Boom']);
});

test('it builds the attribute_models join table from the morph column', function () {
    simulateV1AttributeShape();

    attributeReshapeMigration()->up();

    $rows = DB::table(SPEC0019_PREFIX.'attribute_models')
        ->orderBy('attribute_id')
        ->get(['attribute_id', 'model_type']);

    expect($rows->pluck('model_type', 'attribute_id')->all())->toBe([
        10 => 'product',
        11 => 'product',
        20 => 'brand',
    ]);
});

test('it migrates product_type_attribute and drops attributables', function () {
    simulateV1AttributeShape();

    attributeReshapeMigration()->up();

    $rows = DB::table(SPEC0019_PREFIX.'product_type_attribute')
        ->orderBy('attribute_id')
        ->get(['product_type_id', 'attribute_id']);

    expect($rows->map(fn ($row) => [$row->product_type_id, $row->attribute_id])->all())->toBe([
        [1, 10],
        [1, 11],
    ]);

    expect(Schema::hasTable(SPEC0019_PREFIX.'attributables'))->toBeFalse();
});

test('it converts FieldType FQCN to enum value strings', function () {
    simulateV1AttributeShape();

    attributeReshapeMigration()->up();

    $types = DB::table(SPEC0019_PREFIX.'attributes')->orderBy('id')->pluck('type', 'id');

    expect($types->all())->toBe([
        10 => 'text',
        11 => 'toggle',
        20 => 'text',
    ]);
});

test('it flattens jsonb name columns and reshapes the attribute schema', function () {
    simulateV1AttributeShape();

    attributeReshapeMigration()->up();

    expect(DB::table(SPEC0019_PREFIX.'attributes')->find(10)->name)->toBe('Meta Title');
    expect(DB::table(SPEC0019_PREFIX.'attribute_groups')->find(1)->name)->toBe('Details');

    expect(Schema::hasColumn(SPEC0019_PREFIX.'attributes', 'attribute_type'))->toBeFalse();
    expect(Schema::hasColumn(SPEC0019_PREFIX.'attributes', 'section'))->toBeFalse();
    expect(Schema::hasColumn(SPEC0019_PREFIX.'attributes', 'default_value'))->toBeFalse();
    expect(Schema::hasColumn(SPEC0019_PREFIX.'attribute_groups', 'attributable_type'))->toBeFalse();
    expect(Schema::hasColumn(SPEC0019_PREFIX.'attribute_groups', 'system'))->toBeTrue();
});

test('it converts pipe-delimited validation rules to a json list', function () {
    simulateV1AttributeShape();

    attributeReshapeMigration()->up();

    $rules = DB::table(SPEC0019_PREFIX.'attributes')->pluck('validation_rules', 'id');

    // Split on |, trimmed, empties dropped; blank/absent v1 values stay null.
    expect(json_decode($rules[10], true))->toBe(['min:1', 'max:10'])
        ->and($rules[11])->toBeNull()
        ->and($rules[20])->toBeNull();
});

test('it is idempotent', function () {
    simulateV1AttributeShape();

    attributeReshapeMigration()->up();

    // Second run on a v2-shape DB is a no-op (and must not throw).
    attributeReshapeMigration()->up();

    expect(DB::table(SPEC0019_PREFIX.'attribute_models')->count())->toBe(3);
    expect(DB::table(SPEC0019_PREFIX.'product_type_attribute')->count())->toBe(2);

    $product = DB::table(SPEC0019_PREFIX.'products')->find(1);
    expect(json_decode($product->attribute_data, true))->toBe([
        '10' => 'Trainers',
        '11' => true,
    ]);
});
