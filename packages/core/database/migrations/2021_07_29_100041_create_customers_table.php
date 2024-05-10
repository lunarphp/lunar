<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateCustomersTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'customers', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('company_name')->nullable();
            $table->string('vat_no')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'customers');
    }
}
