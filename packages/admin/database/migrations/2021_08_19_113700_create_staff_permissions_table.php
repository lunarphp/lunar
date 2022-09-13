<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateStaffPermissionsTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'staff_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('staff_id')->constrained(
                $this->prefix.'staff'
            )->cascadeOnDelete();
            $table->string('handle')->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'staff_permissions');
    }
}
