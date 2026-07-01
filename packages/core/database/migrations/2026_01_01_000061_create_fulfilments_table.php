<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'fulfilments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->ulid('public_id')->unique();
            $table->foreignId('order_id')->constrained($this->prefix.'orders')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained($this->prefix.'locations')->restrictOnDelete();
            $table->string('reference')->nullable()->index();
            $table->string('method')->default('shipping')->index()->comment('Fulfilment method key, resolved via FulfilmentMethodManifest');
            $table->string('state')->default('pending')->index();
            $table->text('notes')->nullable();
            $table->jsonb('meta')->nullable();
            $table->dateTime('shipped_at')->nullable()->index();
            $table->dateTime('held_at')->nullable()->index();
            $table->string('hold_reason')->nullable();
            $table->text('hold_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'fulfilments');
    }
};
