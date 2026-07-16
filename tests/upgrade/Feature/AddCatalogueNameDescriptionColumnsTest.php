<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\FieldTypes\Text;
use Lunar\Core\FieldTypes\TranslatedText;
use Lunar\Tests\Upgrade\TestCase;

uses(TestCase::class);

/**
 * An isolated table prefix so this test stands up its own throwaway schema
 * without colliding with (or tearing down) the real `lunar_*` tables other
 * suites share in the same sqlite database.
 */
const SPEC0018_PREFIX = 'upg0018_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => SPEC0018_PREFIX]);
});

afterEach(function () {
    foreach (['attributables', 'products', 'product_types', 'attributes', 'attribute_groups'] as $table) {
        Schema::dropIfExists(SPEC0018_PREFIX.$table);
    }
});

/**
 * Load the spec 0018 data migration's anonymous class instance.
 */
function catalogueMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*add_catalogue_name_description_columns.php');

    return require $path[0];
}

/**
 * Stand up the v1-shaped tables the migration operates on: a `products` table
 * with no dedicated columns (name/description live in `attribute_data`), and
 * the backing system `Attribute` rows.
 */
function simulateV1Products(): void
{
    Schema::create(SPEC0018_PREFIX.'product_types', function (Blueprint $table) {
        $table->id();
        $table->string('name');
    });

    Schema::create(SPEC0018_PREFIX.'attribute_groups', function (Blueprint $table) {
        $table->id();
        $table->string('attributable_type');
        $table->json('name');
        $table->string('handle');
        $table->integer('position');
    });

    Schema::create(SPEC0018_PREFIX.'attributes', function (Blueprint $table) {
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
        $table->json('configuration')->nullable();
        $table->boolean('system')->default(false);
        $table->json('description')->nullable();
        $table->timestamps();
    });

    Schema::create(SPEC0018_PREFIX.'products', function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_type_id');
        $table->string('status');
        $table->json('attribute_data')->nullable();
        $table->timestamps();
    });

    DB::table(SPEC0018_PREFIX.'product_types')->insert(['id' => 1, 'name' => 'Stock']);

    DB::table(SPEC0018_PREFIX.'products')->insert([
        'id' => 1,
        'product_type_id' => 1,
        'status' => 'published',
        'attribute_data' => json_encode([
            'name' => [
                'field_type' => TranslatedText::class,
                'value' => ['en' => 'Trainers', 'fr' => 'Baskets'],
            ],
            'description' => [
                'field_type' => TranslatedText::class,
                'value' => ['en' => 'Comfy'],
            ],
            'meta_title' => [
                'field_type' => Text::class,
                'value' => 'Keep me',
            ],
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table(SPEC0018_PREFIX.'attribute_groups')->insert([
        'id' => 1,
        'attributable_type' => 'product',
        'name' => json_encode(['en' => 'Details']),
        'handle' => 'details',
        'position' => 1,
    ]);

    foreach (['name', 'description'] as $position => $handle) {
        DB::table(SPEC0018_PREFIX.'attributes')->insert([
            'attribute_type' => 'product',
            'attribute_group_id' => 1,
            'position' => $position + 1,
            'name' => json_encode(['en' => ucfirst($handle)]),
            'handle' => $handle,
            'section' => 'main',
            'type' => TranslatedText::class,
            'required' => $handle === 'name',
            'system' => $handle === 'name',
            'configuration' => json_encode(['richtext' => $handle === 'description']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

test('it relaxes a v1 NOT NULL attribute_data column before backfilling', function () {
    Schema::create(SPEC0018_PREFIX.'collections', function (Blueprint $table) {
        $table->id();
        $table->json('attribute_data');
        $table->timestamps();
    });

    DB::table(SPEC0018_PREFIX.'collections')->insert([
        'id' => 1,
        'attribute_data' => json_encode([
            'name' => [
                'field_type' => TranslatedText::class,
                'value' => ['en' => 'Puzzle'],
            ],
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    catalogueMigration()->up();

    $collection = DB::table(SPEC0018_PREFIX.'collections')->find(1);

    expect(json_decode($collection->name, true))->toBe(['en' => 'Puzzle']);
    expect($collection->attribute_data)->toBeNull();

    Schema::drop(SPEC0018_PREFIX.'collections');
});

test('it clears attributables pivot rows before deleting the system attributes', function () {
    simulateV1Products();

    Schema::create(SPEC0018_PREFIX.'attributables', function (Blueprint $table) {
        $table->id();
        $table->foreignId('attribute_id')->constrained(SPEC0018_PREFIX.'attributes');
        $table->string('attributable_type');
        $table->unsignedBigInteger('attributable_id');
    });

    $nameAttributeId = DB::table(SPEC0018_PREFIX.'attributes')->where('handle', 'name')->value('id');
    $descAttributeId = DB::table(SPEC0018_PREFIX.'attributes')->where('handle', 'description')->value('id');

    DB::table(SPEC0018_PREFIX.'attributables')->insert([
        ['attribute_id' => $nameAttributeId, 'attributable_type' => 'product_type', 'attributable_id' => 1],
        ['attribute_id' => $descAttributeId, 'attributable_type' => 'product_type', 'attributable_id' => 1],
    ]);

    catalogueMigration()->up();

    expect(DB::table(SPEC0018_PREFIX.'attributables')->count())->toBe(0);
    expect(DB::table(SPEC0018_PREFIX.'attributes')->whereIn('handle', ['name', 'description'])->count())->toBe(0);
});

test('it wraps plain Text values in the default language', function () {
    simulateV1Products();

    // v1 stores with a single locale commonly used `Text` (a bare string)
    // rather than `TranslatedText` for name/description.
    DB::table(SPEC0018_PREFIX.'products')->where('id', 1)->update([
        'attribute_data' => json_encode([
            'name' => ['field_type' => Text::class, 'value' => 'Contactor'],
            'description' => ['field_type' => Text::class, 'value' => '<p>4kw</p>'],
        ]),
    ]);

    Schema::create(SPEC0018_PREFIX.'languages', function (Blueprint $table) {
        $table->id();
        $table->string('code');
        $table->string('name');
        $table->boolean('default')->default(false);
    });

    DB::table(SPEC0018_PREFIX.'languages')->insert([
        ['code' => 'fr', 'name' => 'French', 'default' => false],
        ['code' => 'de', 'name' => 'German', 'default' => true],
    ]);

    catalogueMigration()->up();

    $product = DB::table(SPEC0018_PREFIX.'products')->find(1);

    expect(json_decode($product->name, true))->toBe(['de' => 'Contactor']);
    expect(json_decode($product->description, true))->toBe(['de' => '<p>4kw</p>']);

    Schema::drop(SPEC0018_PREFIX.'languages');
});

test('it falls back to en when no languages table exists', function () {
    simulateV1Products();

    DB::table(SPEC0018_PREFIX.'products')->where('id', 1)->update([
        'attribute_data' => json_encode([
            'name' => ['field_type' => Text::class, 'value' => 'Contactor'],
        ]),
    ]);

    catalogueMigration()->up();

    $product = DB::table(SPEC0018_PREFIX.'products')->find(1);

    expect(json_decode($product->name, true))->toBe(['en' => 'Contactor']);
});

test('it backfills the dedicated columns from attribute_data', function () {
    simulateV1Products();

    catalogueMigration()->up();

    $product = DB::table(SPEC0018_PREFIX.'products')->find(1);

    expect(json_decode($product->name, true))->toBe(['en' => 'Trainers', 'fr' => 'Baskets']);
    expect(json_decode($product->description, true))->toBe(['en' => 'Comfy']);
    expect($product->short_description)->toBeNull();

    // The promoted keys are stripped, genuinely custom attributes remain.
    $attributeData = json_decode($product->attribute_data, true);
    expect($attributeData)->not->toHaveKey('name');
    expect($attributeData)->not->toHaveKey('description');
    expect($attributeData)->toHaveKey('meta_title');

    // The backing system attribute rows are removed.
    expect(DB::table(SPEC0018_PREFIX.'attributes')->whereIn('handle', ['name', 'description'])->count())->toBe(0);
});
