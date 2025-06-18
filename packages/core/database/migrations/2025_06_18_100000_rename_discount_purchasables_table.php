<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename($this->prefix.'discount_purchasables', $this->prefix.'discountables');

        Schema::table($this->prefix.'discountables', function (Blueprint $table) {
            $table->renameColumn('purchasable_id', 'discountable_id');
        });

        Schema::table($this->prefix.'discountables', function (Blueprint $table) {
            $table->renameColumn('purchasable_type', 'discountable_type');
        });
    }

    public function down(): void
    {
        Schema::rename($this->prefix.'discountables', $this->prefix.'discount_purchasables');

        Schema::table($this->prefix.'discount_purchasables', function (Blueprint $table) {
            $table->renameColumn('discountable_id', 'purchasable_id');
        });

        Schema::table($this->prefix.'discount_purchasables', function (Blueprint $table) {
            $table->renameColumn('discountable_type', 'purchasable_type');
        });
    }
};
