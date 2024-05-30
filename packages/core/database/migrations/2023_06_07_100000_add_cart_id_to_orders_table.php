<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;
use Lunar\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->foreignId('cart_id')->after('user_id')->nullable()->constrained($this->prefix.'carts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['cart_id']);
            }
            $table->dropColumn('cart_id');
        });
    }
};
