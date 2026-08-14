<?php

use Illuminate\Database\Migrations\Migration;
use Lunar\Core\Auth\Manifest;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Seed the spatie permission gating the blog panel section, on the same
     * guard the panel authenticates against (the `staff` guard, resolved from
     * Lunar's Manifest rather than hardcoded).
     */
    public function up(): void
    {
        Permission::findOrCreate(config('lunar-blog.permission'), app(Manifest::class)->getAuthGuard());
    }

    public function down(): void
    {
        Permission::query()
            ->where('name', config('lunar-blog.permission'))
            ->where('guard_name', app(Manifest::class)->getAuthGuard())
            ->delete();
    }
};
