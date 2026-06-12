<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'checkout_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Public capability token — the external identifier. Never expose `id`.
            $table->uuid()->unique();
            $table->string('channel_handle')->index();
            $table->string('currency_code', 3);
            $table->string('locale')->nullable();
            $table->string('cart_reference');
            // Mirrors cart_reference while Open/PaymentProcessing, NULLed in the
            // same UPDATE as any terminal transition. The plain unique index is
            // the portable one-active-session-per-cart guarantee (0010 §F.2) —
            // multiple NULLs are allowed on MySQL, Postgres and SQLite alike.
            $table->string('active_cart_reference')->nullable()->unique();
            $table->string('cart_fingerprint')->index();

            $table->unsignedBigInteger('amount_subtotal');
            $table->unsignedBigInteger('amount_total');

            $table->string('status')->default('open')->index();

            $table->string('customer_reference')->nullable();
            $table->string('customer_email')->nullable();

            // Gateway-agnostic in-flight intent handle + merchant correlation id.
            // Unique so webhook → session resolution is unambiguous (0010 §H).
            $table->string('payment_intent_ref')->nullable()->unique();
            $table->string('client_reference_id')->nullable()->index();

            $table->string('order_reference')->nullable()->index();

            // Hosted-checkout return URLs (validated against an allowlist).
            $table->string('success_url')->nullable();
            $table->string('cancel_url')->nullable();

            // Merchant-supplied (echoed back) vs Lunar-internal snapshot extras.
            $table->json('element_data')->nullable();
            $table->json('metadata')->nullable();
            $table->json('meta')->nullable();

            // Bounded-reconciliation bookkeeping (0010 §F). payment_processing_at
            // is the sweep's age anchor (updated_at self-re-arms on any write).
            $table->unsignedTinyInteger('reconciliation_attempts')->default(0);

            $table->dateTime('payment_processing_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->dateTime('pruned_at')->nullable();

            $table->index(['cart_reference', 'status']);
            $table->index(['status', 'expires_at']);
            $table->index(['status', 'payment_processing_at']);
            $table->index(['status', 'pruned_at']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'checkout_sessions');
    }
};
