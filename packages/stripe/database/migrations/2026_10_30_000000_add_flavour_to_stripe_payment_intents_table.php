<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * The flavour column was added by editing the create migration, so a
 * database migrated before that edit never received it and every intent
 * lookup fails. Adds it when missing; a database that has it is untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn($this->prefix.'stripe_payment_intents', 'flavour')) {
            return;
        }

        Schema::table($this->prefix.'stripe_payment_intents', function (Blueprint $table) {
            $table->string('flavour')->default('standard');
        });
    }

    public function down(): void
    {
        // Nothing to reverse: on most databases the column belongs to the
        // create migration, and dropping it here would break that.
    }
};
