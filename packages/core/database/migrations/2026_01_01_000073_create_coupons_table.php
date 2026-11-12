<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained($this->prefix.'discounts')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->unsignedInteger('uses')->default(0);
            $table->unsignedInteger('max_uses')->nullable();
            $table->string('email')->nullable()->index();
            $table->ulid('batch')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'coupons');
    }
};
