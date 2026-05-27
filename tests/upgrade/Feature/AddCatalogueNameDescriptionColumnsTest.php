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
    foreach (['products', 'product_types', 'attributes', 'attribute_groups'] as $table) {
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
