<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'customers', function (Blueprint $table) {
            $table->index('title');
            $table->index('first_name');
            $table->index('last_name');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'customers', function (Blueprint $table) {
            $table->dropIndex($this->prefix . 'customers_title_index');
            $table->dropIndex($this->prefix . 'customers_first_name_index');
            $table->dropIndex($this->prefix . 'customers_last_name_index');
        });
    }
};
