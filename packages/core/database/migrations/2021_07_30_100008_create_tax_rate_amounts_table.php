<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class CreateTaxRateAmountsTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'tax_rate_amounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_class_id')->nullable()->constrained($this->prefix.'tax_classes');
            $table->foreignId('tax_rate_id')->nullable()->constrained($this->prefix.'tax_rates');
            $table->decimal('percentage', 7, 3)->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->prefix.'tax_rate_amounts');
    }
}
