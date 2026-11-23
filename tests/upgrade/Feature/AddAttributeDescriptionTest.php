<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Tests\Upgrade\TestCase;

uses(TestCase::class)->group('cross-db');

/**
 * Isolated prefix so this stands up its own throwaway schema without touching
 * the shared lunar_* tables.
 */
const ATTRIBUTE_DESCRIPTION_UPG_PREFIX = 'upgattrdesc_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => ATTRIBUTE_DESCRIPTION_UPG_PREFIX]);
});

afterEach(function () {
    Schema::dropIfExists(ATTRIBUTE_DESCRIPTION_UPG_PREFIX.'attributes');
});

function attributeDescriptionUpgradeMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*add_attribute_description.php');

    expect($path)->toHaveCount(1);

    return require $path[0];
}

test('it flattens a v1 translated description to one string', function () {
    Schema::create(ATTRIBUTE_DESCRIPTION_UPG_PREFIX.'attributes', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->json('description')->nullable();
    });

    DB::table(ATTRIBUTE_DESCRIPTION_UPG_PREFIX.'attributes')->insert([
        ['id' => 1, 'name' => 'Material', 'description' => json_encode(['fr' => 'Matière', 'en' => 'What it is made of'])],
        ['id' => 2, 'name' => 'Care', 'description' => json_encode(['de' => 'Pflegehinweise'])],
        ['id' => 3, 'name' => 'Story', 'description' => json_encode(['en' => str_repeat('a', 300)])],
        ['id' => 4, 'name' => 'Blank', 'description' => json_encode(['en' => ''])],
        ['id' => 5, 'name' => 'None', 'description' => null],
    ]);

    attributeDescriptionUpgradeMigration()->up();
    attributeDescriptionUpgradeMigration()->up();

    $descriptions = DB::table(ATTRIBUTE_DESCRIPTION_UPG_PREFIX.'attributes')->orderBy('id')->pluck('description', 'id');

    expect($descriptions->all())->toBe([
        1 => 'What it is made of',
        2 => 'Pflegehinweise',
        3 => str_repeat('a', 255),
        4 => null,
        5 => null,
    ]);

    // Now a plain string column, so text that is not JSON can be stored.
    DB::table(ATTRIBUTE_DESCRIPTION_UPG_PREFIX.'attributes')->where('id', 5)->update(['description' => 'Plain text']);

    expect(DB::table(ATTRIBUTE_DESCRIPTION_UPG_PREFIX.'attributes')->where('id', 5)->value('description'))->toBe('Plain text');
});

test('it adds the column when the attributes table has none', function () {
    Schema::create(ATTRIBUTE_DESCRIPTION_UPG_PREFIX.'attributes', function (Blueprint $table) {
        $table->id();
        $table->string('name');
    });

    DB::table(ATTRIBUTE_DESCRIPTION_UPG_PREFIX.'attributes')->insert(['id' => 1, 'name' => 'Material']);

    attributeDescriptionUpgradeMigration()->up();
    attributeDescriptionUpgradeMigration()->up();

    expect(Schema::hasColumn(ATTRIBUTE_DESCRIPTION_UPG_PREFIX.'attributes', 'description'))->toBeTrue()
        ->and(DB::table(ATTRIBUTE_DESCRIPTION_UPG_PREFIX.'attributes')->value('description'))->toBeNull();
});

test('it leaves an existing string description untouched', function () {
    Schema::create(ATTRIBUTE_DESCRIPTION_UPG_PREFIX.'attributes', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('description')->nullable();
    });

    DB::table(ATTRIBUTE_DESCRIPTION_UPG_PREFIX.'attributes')->insert(['id' => 1, 'name' => 'Material', 'description' => '{"en":"Kept as written"}']);

    attributeDescriptionUpgradeMigration()->up();

    expect(DB::table(ATTRIBUTE_DESCRIPTION_UPG_PREFIX.'attributes')->value('description'))->toBe('{"en":"Kept as written"}');
});

test('it is a no-op when the attributes table does not exist', function () {
    attributeDescriptionUpgradeMigration()->up();

    expect(Schema::hasTable(ATTRIBUTE_DESCRIPTION_UPG_PREFIX.'attributes'))->toBeFalse();
});
