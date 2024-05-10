<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateCustomerUserTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'customer_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained($this->prefix.'customers');
            $table->userForeignKey();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'customer_user');
    }
}
