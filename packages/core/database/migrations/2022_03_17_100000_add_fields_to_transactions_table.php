<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddFieldsToTransactionsTable extends Migration
{
    public function up()
    {
        Schema::table($this->prefix.'transactions', function (Blueprint $table) {
            $table->foreignId('parent_transaction_id')->after('id')
                ->nullable()
                ->constrained($this->prefix.'transactions');
            $table->dateTime('captured_at')->nullable()->index();
            $table->enum('type', ['refund', 'intent', 'capture'])->after('success')->index()->default('capture');
        });

        Schema::table($this->prefix.'transactions', function (Blueprint $table) {
            $table->dropColumn('refund');
        });
    }

    public function down()
    {
        Schema::table($this->prefix.'transactions', function ($table) {
            $table->dropForeign(['parent_transaction_id']);
            $table->dropColumn('parent_transaction_id');
            $table->dropColumn('type');
        });

        Schema::table($this->prefix.'transactions', function ($table) {
            $table->boolean('refund')->default(false)->index();
        });
    }
}
