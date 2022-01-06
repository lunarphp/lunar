<?php

use GetCandy\Base\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartsTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'carts', function (Blueprint $table) {
            $userModel = config('auth.providers.users.model');

            $table->bigIncrements('id');
            $table->foreignId('user_id')->nullable()->constrained(
                (new $userModel)->getTable()
            );
            $table->foreignId('merged_id')->nullable()->constrained($this->prefix.'carts');
            $table->foreignId('currency_id')->constrained($this->prefix.'currencies');
            $table->foreignId('channel_id')->constrained($this->prefix.'channels');
            $table->foreignId('order_id')->nullable()->constrained($this->prefix.'orders');
            $table->string('coupon_code')->index()->nullable();
            $table->dateTime('completed_at')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'carts');
    }
}
