<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('media')) {
            return;
        }

        Schema::create('media', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('model');
            $table->uuid('uuid')->nullable()->unique();
            $table->string('collection_name');
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->string('disk');
            $table->string('conversions_disk')->nullable();
            $table->unsignedBigInteger('size');
            $table->jsonb('manipulations');
            $table->jsonb('custom_properties');
            $table->jsonb('generated_conversions');
            $table->jsonb('responsive_images');
            $table->unsignedInteger('order_column')->nullable();
            $table->nullableTimestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
