<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'customer_group_discount', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained($this->prefix.'discounts');
            $table->foreignId('customer_group_id')->constrained($this->prefix.'customer_groups');
            $table->scheduling();
            $table->boolean('visible')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'customer_group_discount');
    }
};
