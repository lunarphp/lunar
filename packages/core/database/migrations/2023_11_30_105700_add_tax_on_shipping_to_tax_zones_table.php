<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

class AddTaxOnShippingToTaxZonesTable extends Migration
{
    public function up(): void
    {
        Schema::table('lunar_tax_zones', function (Blueprint $table) {
            $table
                ->boolean('tax_on_shipping')
                ->default(false)
                ->after('default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lunar_tax_zones', function (Blueprint $table) {
            $table->dropColumn('tax_on_shipping');
        });
    }
}
