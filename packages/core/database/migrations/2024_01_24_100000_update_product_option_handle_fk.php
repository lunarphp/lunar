<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'product_options', function (Blueprint $table) {
            $table->dropUnique(
                $this->prefix.'product_options_handle_unique'
            );
        });

        Schema::table($this->prefix.'product_options', function (Blueprint $table) {
            $table->index('handle');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'product_options', function (Blueprint $table) {
            $table->dropIndex(
                $this->prefix.'product_options_handle_index'
            );
        });

        Schema::table($this->prefix.'product_options', function (Blueprint $table) {
            $table->unique('handle');
        });
    }
};
