<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'carts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->ulid('public_id')->unique();
            $table->userForeignKey(nullable: true);
            $table->foreignId('merged_id')->nullable()->constrained($this->prefix.'carts');
            $table->foreignId('currency_id')->constrained($this->prefix.'currencies');
            $table->foreignId('channel_id')->constrained($this->prefix.'channels');
            $table->foreignId('order_id')->nullable()->constrained($this->prefix.'orders');
            $table->string('coupon_code')->index()->nullable();
            $table->dateTime('completed_at')->nullable()->index();
            $table->jsonb('meta')->nullable();
            $table->timestamps();
            $table->foreignId('customer_id')->nullable()->constrained($this->prefix.'customers');
            $table->softDeletes();
            $table->foreignId('tax_zone_id')->nullable()->constrained($this->prefix.'tax_zones')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'carts');
    }
};
