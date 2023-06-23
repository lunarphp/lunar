<?php

use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class DropStaffPermissionsTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists($this->prefix.'staff_permissions');
    }

    public function down()
    {
        //
    }
}
