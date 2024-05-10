<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;
use Lunar\Facades\DB;

class AddCustomerIdToOrdersTable extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->foreignId('customer_id')->after('id')
                ->nullable()
                ->constrained($this->prefix.'customers');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'orders', function ($table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['customer_id']);
            }
            $table->dropColumn('customer_id');
        });
    }
}
