<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_article_related_product', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained('blog_articles')->cascadeOnDelete();
            // FK cascade so a deleted product drops the link; the reader also
            // omits missing products at render time.
            $table->foreignId('product_id')->constrained('lunar_products')->cascadeOnDelete();
            $table->primary(['article_id', 'product_id']);
        });

        Schema::create('blog_article_related_article', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained('blog_articles')->cascadeOnDelete();
            $table->foreignId('related_article_id')->constrained('blog_articles')->cascadeOnDelete();
            $table->primary(['article_id', 'related_article_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_article_related_article');
        Schema::dropIfExists('blog_article_related_product');
    }
};
