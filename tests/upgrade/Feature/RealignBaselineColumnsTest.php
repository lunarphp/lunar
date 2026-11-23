<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Tests\Upgrade\TestCase;

uses(TestCase::class)->group('cross-db');

const REALIGN_PREFIX = 'upgra_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => REALIGN_PREFIX]);
});

afterEach(function () {
    Schema::withoutForeignKeyConstraints(function () {
        foreach (['order_lines', 'addresses', 'customers', 'product_options', 'attribute_models', 'attributes', 'attribute_groups', 'products', 'collections', 'languages'] as $table) {
            Schema::dropIfExists(REALIGN_PREFIX.$table);
        }
    });
});

function realignMigration(string $name): object
{
    $path = glob(dirname(__DIR__, 3)."/packages/upgrade/database/migrations/*{$name}.php");

    return require $path[0];
}

test('it adds the columns folded into baselines during the alpha', function () {
    Schema::create(REALIGN_PREFIX.'order_lines', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('order_id');
    });
    Schema::create(REALIGN_PREFIX.'addresses', fn (Blueprint $table) => $table->id());
    Schema::create(REALIGN_PREFIX.'customers', fn (Blueprint $table) => $table->id());
    Schema::create(REALIGN_PREFIX.'product_options', fn (Blueprint $table) => $table->id());

    DB::table(REALIGN_PREFIX.'order_lines')->insert(['id' => 1, 'order_id' => 1]);
    DB::table(REALIGN_PREFIX.'product_options')->insert(['id' => 1]);

    foreach (['add_order_line_parent_line_id', 'add_address_last_used_at', 'add_customer_admin_notes', 'add_product_option_type'] as $name) {
        realignMigration($name)->up();
        realignMigration($name)->up();
    }

    expect(DB::table(REALIGN_PREFIX.'order_lines')->value('parent_line_id'))->toBeNull()
        ->and(Schema::hasColumn(REALIGN_PREFIX.'addresses', 'last_used_at'))->toBeTrue()
        ->and(Schema::hasColumn(REALIGN_PREFIX.'customers', 'admin_notes'))->toBeTrue()
        ->and(DB::table(REALIGN_PREFIX.'product_options')->value('type'))->toBe('text');

    if (DB::getDriverName() === 'sqlite') {
        return;
    }

    // A component line is deleted with the line it belongs to.
    DB::table(REALIGN_PREFIX.'order_lines')->insert(['id' => 2, 'order_id' => 1, 'parent_line_id' => 1]);
    DB::table(REALIGN_PREFIX.'order_lines')->where('id', 1)->delete();

    expect(DB::table(REALIGN_PREFIX.'order_lines')->count())->toBe(0);
});

test('it realigns the v1 attribute columns with the baseline', function () {
    Schema::create(REALIGN_PREFIX.'attribute_groups', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->integer('position');
    });
    Schema::create(REALIGN_PREFIX.'attributes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('attribute_group_id')->nullable()->constrained(REALIGN_PREFIX.'attribute_groups');
        $table->string('handle');
        $table->integer('position');
        $table->json('configuration');
        $table->boolean('required');
        $table->boolean('searchable')->default(true);
        $table->boolean('system');
    });
    Schema::create(REALIGN_PREFIX.'attribute_models', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('attribute_id');
        $table->string('model_type');
    });

    DB::table(REALIGN_PREFIX.'attribute_groups')->insert(['id' => 1, 'name' => 'Details', 'position' => 1]);
    DB::table(REALIGN_PREFIX.'attributes')->insert([
        ['id' => 1, 'attribute_group_id' => 1, 'handle' => 'material', 'position' => 1, 'configuration' => '{}', 'required' => false, 'system' => false],
        ['id' => 2, 'attribute_group_id' => 1, 'handle' => 'material', 'position' => 2, 'configuration' => '{}', 'required' => false, 'system' => false],
    ]);
    DB::table(REALIGN_PREFIX.'attribute_models')->insert([
        ['attribute_id' => 1, 'model_type' => 'product'],
        ['attribute_id' => 2, 'model_type' => 'collection'],
    ]);

    realignMigration('realign_attribute_columns')->up();
    realignMigration('realign_attribute_columns')->up();

    // The oldest attribute keeps a shared handle; the other is renamed.
    expect(DB::table(REALIGN_PREFIX.'attributes')->orderBy('id')->pluck('handle')->all())
        ->toBe(['material', 'material_collection']);

    // A create that leaves the defaulted columns out takes the baseline defaults.
    DB::table(REALIGN_PREFIX.'attributes')->insert(['id' => 3, 'handle' => 'colour']);
    DB::table(REALIGN_PREFIX.'attribute_groups')->insert(['id' => 2, 'name' => 'Extra']);

    $created = DB::table(REALIGN_PREFIX.'attributes')->find(3);
    expect((bool) $created->required)->toBeFalse()
        ->and((bool) $created->searchable)->toBeFalse()
        ->and((bool) $created->system)->toBeFalse()
        ->and((int) $created->position)->toBe(1)
        ->and($created->configuration)->toBeNull()
        ->and((int) DB::table(REALIGN_PREFIX.'attribute_groups')->find(2)->position)->toBe(1);

    expect(fn () => DB::table(REALIGN_PREFIX.'attributes')->insert(['handle' => 'colour']))
        ->toThrow(QueryException::class);

    // Deleting a group leaves its attributes ungrouped.
    DB::table(REALIGN_PREFIX.'attribute_groups')->where('id', 1)->delete();

    expect(DB::table(REALIGN_PREFIX.'attributes')->whereNotNull('attribute_group_id')->count())->toBe(0);
})->skip(fn () => DB::getDriverName() === 'sqlite', 'SQLite does not enforce the foreign key this asserts');

test('it makes catalogue names required and defaults product status to draft', function () {
    Schema::create(REALIGN_PREFIX.'languages', function (Blueprint $table) {
        $table->id();
        $table->string('code');
        $table->boolean('default');
    });
    Schema::create(REALIGN_PREFIX.'products', function (Blueprint $table) {
        $table->id();
        $table->string('status');
        $table->jsonb('name')->nullable();
    });
    Schema::create(REALIGN_PREFIX.'collections', function (Blueprint $table) {
        $table->id();
        $table->jsonb('name')->nullable();
    });

    DB::table(REALIGN_PREFIX.'languages')->insert([
        ['code' => 'en', 'default' => false],
        ['code' => 'fr', 'default' => true],
    ]);
    DB::table(REALIGN_PREFIX.'products')->insert([
        ['id' => 1, 'status' => 'published', 'name' => json_encode(['fr' => 'Chaise'])],
        ['id' => 2, 'status' => 'published', 'name' => null],
    ]);
    DB::table(REALIGN_PREFIX.'collections')->insert(['id' => 1, 'name' => null]);

    realignMigration('realign_catalogue_columns')->up();
    realignMigration('realign_catalogue_columns')->up();

    $names = DB::table(REALIGN_PREFIX.'products')->orderBy('id')->pluck('name')
        ->map(fn ($name) => json_decode($name, true))->all();

    expect($names)->toBe([['fr' => 'Chaise'], ['fr' => '']])
        ->and(json_decode(DB::table(REALIGN_PREFIX.'collections')->value('name'), true))->toBe(['fr' => '']);

    DB::table(REALIGN_PREFIX.'products')->insert(['id' => 3, 'name' => json_encode(['fr' => 'Table'])]);

    expect(DB::table(REALIGN_PREFIX.'products')->find(3)->status)->toBe('draft')
        ->and(fn () => DB::table(REALIGN_PREFIX.'collections')->insert(['name' => null]))
        ->toThrow(QueryException::class);
});
