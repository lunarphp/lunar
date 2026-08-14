<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Scalar content fields for articles. Categories, tags, related
     * products/articles, and the featured image land in later migrations with
     * their own relations and media handling.
     */
    public function up(): void
    {
        Schema::table('blog_articles', function (Blueprint $table) {
            $table->text('excerpt')->nullable()->after('slug');
            $table->string('seo_title')->nullable()->after('body');
            $table->text('seo_description')->nullable()->after('seo_title');
        });
    }

    public function down(): void
    {
        Schema::table('blog_articles', function (Blueprint $table) {
            $table->dropColumn(['excerpt', 'seo_title', 'seo_description']);
        });
    }
};
