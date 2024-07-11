<?php

namespace Lunar\Base;

use Illuminate\Database\Migrations\Migration as BaseMigration;

abstract class Migration extends BaseMigration
{
    /**
     * Migration table prefix.
     */
    protected string $prefix = '';

    /**
     * Create a new instance of the migration.
     */
    public function __construct()
    {
        $this->prefix = config('lunar.database.table_prefix');
    }

    /**
     * Use the connection specified in config.
     */
    public function getConnection(): ?string
    {
        if ($connection = config('lunar.database.connection')) {
            return $connection;
        }

        return parent::getConnection();
    }

    public function canDropForeignKeys(): bool
    {
        return can_drop_foreign_keys();
    }
}
