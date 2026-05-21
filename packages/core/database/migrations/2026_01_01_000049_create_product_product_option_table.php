<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'product_product_option', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained($this->prefix.'products');
            $table->foreignId('product_option_id')->constrained($this->prefix.'product_options');
            $table->smallInteger('position')->index();
            $table->unique(['product_id', 'product_option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'product_product_option');
    }
};
