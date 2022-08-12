<?php

use GetCandy\Base\Migration;
use GetCandy\Models\ProductVariant;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaVariantTable extends Migration
{
    public function up()
    {
        Schema::create($this->prefix.'media_product_variant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained()->onDelete('cascade');
            $table->foreignIdFor(ProductVariant::class)->constrained()->onDelete('cascade');
            $table->boolean('primary')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        // Schema::table($this->prefix.'transactions', function ($table) {
        //     $table->smallInteger('last_four')->nullable(false)->change();
        // });
    }
}
