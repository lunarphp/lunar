<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix.'product_associations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_parent_id')->constrained($this->prefix.'products');
            $table->foreignId('product_target_id')->constrained($this->prefix.'products');
            $table->string('type')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'product_associations');
    }
};
