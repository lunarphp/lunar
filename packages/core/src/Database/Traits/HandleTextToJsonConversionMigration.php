<?php

namespace Lunar\Database\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait HandleTextToJsonConversionMigration
{
    /**
     * @var string The table name.
     */
    protected string $table = '';

    /**
     * @var string The name of the field to convert.
     */
    protected string $fieldName = '';

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function setup()
    {
        $this->prefix = config('lunar.database.table_prefix');

        if (blank($this->table) || blank($this->fieldName)) {
            throw new \Exception('Both table and field names must be set.');
        }

        if (! Schema::hasTable($this->prefix.$this->table)) {
            throw new \Exception('Table does not exist.');
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->prefix.$this->table, function (Blueprint $table) {
            $table->json("{$this->fieldName}_json")->nullable()->after($this->fieldName);
        });

        $this->updateCustomerGroupsName("{$this->fieldName}_json");

        Schema::table($this->prefix.$this->table, function (Blueprint $table) {
            $table->dropColumn($this->fieldName);
        });

        Schema::table($this->prefix.$this->table, function (Blueprint $table) {
            $table->renameColumn("{$this->fieldName}_json", $this->fieldName);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->prefix.$this->table, function (Blueprint $table) {
            $table->string($this->fieldName)->change();
        });

        $this->updateCustomerGroupsName($this->fieldName, 'text');
    }

    protected function updateCustomerGroupsName(string $field = 'name', string $fieldType = 'json'): void
    {
        $locale = app()->getLocale();
        $groups = DB::table($this->prefix.$this->table)->get();
        foreach ($groups as $group) {
            DB::table($this->prefix.$this->table)->where('id', $group->id)->update([
                $field => $fieldType === 'json'
                    ? json_encode([$locale => $group->name])
                    : json_decode($group->name, true)[$locale],
            ]);
        }
    }
}
