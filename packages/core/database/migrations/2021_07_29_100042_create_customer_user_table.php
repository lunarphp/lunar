<?php

use GetCandy\Base\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->prefix.'customer_user', function (Blueprint $table) {
            $userModel = config('auth.providers.users.model');

            $table->id();
            $table->foreignId('customer_id')->constrained($this->prefix.'customers');
            $table->foreignId('user_id')->constrained(
                (new $userModel())->getTable()
            );
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
        Schema::dropIfExists($this->prefix.'customer_user');
    }
}
