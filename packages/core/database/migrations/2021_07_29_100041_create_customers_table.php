<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->prefix.'customers', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('company_name')->nullable();
            $table->string('vat_no')->nullable();
            $table->string('account_ref')->nullable()->index();
            $table->json('attribute_data')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->prefix.'customers');
    }
}
