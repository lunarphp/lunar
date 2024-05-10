<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class SetLastFourToNullableOnTransactions extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'transactions', function (Blueprint $table) {
            $table->string('last_four')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'transactions', function ($table) {
            $table->string('last_four')->nullable(false)->change();
        });
    }
}
