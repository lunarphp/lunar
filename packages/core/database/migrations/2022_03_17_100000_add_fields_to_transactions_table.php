<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'transactions', function (Blueprint $table) {
            $table->foreignId('parent_transaction_id')->after('id')
                ->nullable()
                ->constrained($this->prefix.'transactions');
            $table->dateTime('captured_at')->nullable()->index();
            $table->enum('type', ['refund', 'intent', 'capture'])->after('success')->index()->default('capture');
        });

        Schema::table($this->prefix.'transactions', function (Blueprint $table) {
            $table->dropIndex(['refund']);
            $table->dropColumn('refund');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'transactions', function (Blueprint $table) {
            if ($this->canDropForeignKeys()) {
                $table->dropForeign(['parent_transaction_id']);
            }
            $table->dropIndex(['type']);
            $table->dropColumn(['parent_transaction_id', 'type']);
        });

        Schema::table($this->prefix.'transactions', function (Blueprint $table) {
            $table->boolean('refund')->default(false)->index();
        });
    }
};
