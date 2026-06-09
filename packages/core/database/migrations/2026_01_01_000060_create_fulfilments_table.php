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
            $table->foreignId('order_id')->constrained($this->prefix.'orders')->cascadeOnDelete();
            $table->string('reference')->nullable()->index();
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
