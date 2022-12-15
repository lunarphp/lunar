<?php

use Lunar\Base\Migration;
use Lunar\Database\Traits\HandleTextToJsonConversionMigration;

class UpdateNameOnCustomerGroupsTable extends Migration
{
    use HandleTextToJsonConversionMigration;

    public function __construct()
    {
        $this->table = 'customer_groups';
        $this->fieldName = 'name';

        parent::__construct();
    }
}
