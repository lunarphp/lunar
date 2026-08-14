<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_articles', function (Blueprint $table) {
            // The image itself is a spatie media record; only its alt text is a
            // column on the article.
            $table->string('featured_image_alt')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('blog_articles', function (Blueprint $table) {
            $table->dropColumn('featured_image_alt');
        });
    }
};
