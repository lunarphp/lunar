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

            // Persisted totals snapshot (spec 0076). NULL means "not calculated".
            $table->unsignedBigInteger('sub_total')->nullable();
            $table->unsignedBigInteger('sub_total_discounted')->nullable();
            $table->unsignedBigInteger('discount_total')->nullable();
            $table->unsignedBigInteger('shipping_sub_total')->nullable();
            $table->unsignedBigInteger('shipping_tax_total')->nullable();
            $table->unsignedBigInteger('shipping_total')->nullable();
            $table->unsignedBigInteger('tax_total')->nullable();
            $table->unsignedBigInteger('total')->nullable();
            $table->jsonb('tax_breakdown')->nullable();
            $table->jsonb('shipping_breakdown')->nullable();
            $table->jsonb('discount_breakdown')->nullable();
            $table->jsonb('free_items')->nullable();
            $table->unsignedInteger('revision')->default(0);
            $table->unsignedInteger('calculated_revision')->nullable();
            $table->timestamp('calculated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'carts');
    }
};
