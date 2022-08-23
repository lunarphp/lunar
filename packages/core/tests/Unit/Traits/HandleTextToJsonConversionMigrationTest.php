<?php

namespace GetCandy\Tests\Unit\Traits;

use GetCandy\Base\Migration;
use GetCandy\Database\Traits\HandleTextToJsonConversionMigration;
use GetCandy\Models\CustomerGroup;
use GetCandy\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Schema;

/**
 * @group traits
 */
class HandleTextToJsonConversionMigrationTest extends TestCase
{
    use WithFaker;

    protected $customerGroupMigration;

    public function setUp(): void
    {
        parent::setUp();

        $this->customerGroupMigration = new class extends Migration
        {
            use HandleTextToJsonConversionMigration;

            public function __construct()
            {
                parent::__construct();

                $this->table = 'customer_groups';
                $this->fieldName = 'name';

                Schema::create($this->prefix.$this->table, function (Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->string('handle')->unique();
                    $table->timestamps();
                });
            }
        };
    }

    /** @test */
    public function throw_exception_if_table_and_field_are_not_set()
    {
        $migration = new class
        {
            use HandleTextToJsonConversionMigration;
        };

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Both table and field names must be set.');
        $migration->setup();
    }

    /** @test */
    public function throw_exception_if_table_does_not_exist()
    {
        $migration = new class extends Migration
        {
            use HandleTextToJsonConversionMigration;

            public function __construct()
            {
                parent::__construct();

                $this->table = 'invalid_table';
                $this->fieldName = 'name';

                Schema::create($this->prefix.'channels', function (Blueprint $table) {
                    $table->id();
                    $table->string('name');
                });
            }
        };

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Table does not exist.');

        $migration->setup();
    }

    /** @test */
    public function can_migrate_customer_group_converting_name_to_json()
    {
        $this->customerGroupMigration->setup();

        $groupNames = CustomerGroup::factory()->count(10)->create([
            'name' => 'Translatable Group ('.$this->faker->randomLetter.')',
        ])->collect()->map(function (CustomerGroup $group) {
            return $group->getRawOriginal('name');
        });

        $groupNames->each(function ($groupName) {
            $this->assertDatabaseHas(config('getcandy.database.table_prefix').'customer_groups', [
                'name' => $groupName,
            ]);
        });

        $this->customerGroupMigration->up();

        CustomerGroup::all()->each(function (CustomerGroup $group) {
            $this->assertJson($group->name);
            $this->assertDatabaseHas(config('getcandy.database.table_prefix').'customer_groups', [
                'name' => json_encode($group->name),
            ]);
        });
    }

    /** @test */
    public function can_rollback_customer_group_converting_json_to_name()
    {
        CustomerGroup::factory()->count(10)->create([
            'name' => collect(['en' => 'Translatable Group ('.$this->faker->randomLetter.')']),
        ])->collect();

        $this->customerGroupMigration->down();

        CustomerGroup::all()->each(function (CustomerGroup $group) {
            $name = $group->getRawOriginal('name');
            $this->assertIsString($name);
            $this->assertDatabaseHas(config('getcandy.database.table_prefix').'customer_groups', [
                'name' => $name,
            ]);
        });
    }
}
