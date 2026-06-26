<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained($this->prefix.'product_variants')->cascadeOnDelete();
            $table->integer('quantity');
            $table->nullableMorphs('reference'); // the holder — a Cart / checkout session in the follow-on
            $table->timestamp('expires_at')->nullable()->index(); // null = no auto-expiry
            $table->timestamp('released_at')->nullable();
            $table->timestamp('committed_at')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'stock_reservations');
    }
};
