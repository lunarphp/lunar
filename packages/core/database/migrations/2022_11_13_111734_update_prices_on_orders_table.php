<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_total')->change();
            $table->unsignedBigInteger('discount_total')->change();
            $table->unsignedBigInteger('shipping_total')->change();
            $table->unsignedBigInteger('tax_total')->change();
            $table->unsignedBigInteger('total')->change();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->unsignedInteger('sub_total')->change();
            $table->unsignedInteger('discount_total')->change();
            $table->unsignedInteger('shipping_total')->change();
            $table->unsignedInteger('tax_total')->change();
            $table->unsignedInteger('total')->change();
        });
    }
};
