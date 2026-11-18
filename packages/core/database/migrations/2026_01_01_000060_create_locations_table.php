<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'locations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->ulid('public_id')->unique();
            $table->string('name');
            $table->string('handle')->unique();
            $table->boolean('default')->default(false)->index();
            $table->string('line_one')->nullable();
            $table->string('line_two')->nullable();
            $table->string('line_three')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postcode')->nullable();
            $table->foreignId('country_id')->nullable()->constrained($this->prefix.'countries')->nullOnDelete();
            $table->string('contact_phone')->nullable();
            $table->jsonb('meta')->nullable();
            $table->timestamps();
        });

        // Carts and orders are created before locations, so their pickup location keys are constrained here.
        foreach (['carts', 'orders'] as $name) {
            Schema::table($this->prefix.$name, function (Blueprint $table) {
                $table->foreign('pickup_location_id')->references('id')->on($this->prefix.'locations')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['carts', 'orders'] as $name) {
            Schema::table($this->prefix.$name, function (Blueprint $table) {
                $table->dropForeign(['pickup_location_id']);
            });
        }

        Schema::dropIfExists($this->prefix.'locations');
    }
};
