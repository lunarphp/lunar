<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'customers', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('title')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('company_name')->nullable();
            $table->string('tax_identifier')->nullable();
            $table->jsonb('meta')->nullable();
            $table->timestamps();
            $table->jsonb('attribute_data')->nullable();
            $table->string('account_ref')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'customers');
    }
};
