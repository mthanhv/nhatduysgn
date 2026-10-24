<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Products from other old sites (config legacy.product_sites) keep their old ID as "site:id", e.g. "tbn:107",
     * because their numeric IDs overlap with nhatduysgn.com's legacy_id.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('legacy_key', 40)->nullable()->unique()->after('legacy_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['legacy_key']);
            $table->dropColumn('legacy_key');
        });
    }
};
