<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table($this->prefix.'product_types', function (Blueprint $table) {
            $table->attributeData();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->prefix.'product_types', function (Blueprint $table) {
            $table->dropColumn('attribute_data');
        });
    }
};