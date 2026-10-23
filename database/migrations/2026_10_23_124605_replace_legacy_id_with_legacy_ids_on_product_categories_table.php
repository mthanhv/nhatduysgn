<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A group can now absorb several old site categories, so the single legacy_id becomes a list.
     */
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            // Old site category IDs merged into this group, as strings: ["85","113"].
            $table->json('legacy_ids')->nullable()->after('meta_description');
        });

        DB::table('product_categories')->whereNotNull('legacy_id')->orderBy('id')->each(function (object $row) {
            DB::table('product_categories')->where('id', $row->id)->update(['legacy_ids' => json_encode([(string) $row->legacy_id])]);
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropUnique(['legacy_id']);
            $table->dropColumn('legacy_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->unsignedInteger('legacy_id')->nullable()->unique();
        });

        DB::table('product_categories')->whereNotNull('legacy_ids')->orderBy('id')->each(function (object $row) {
            $first = json_decode($row->legacy_ids, true)[0] ?? null;
            DB::table('product_categories')->where('id', $row->id)->update(['legacy_id' => $first]);
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropColumn('legacy_ids');
        });
    }
};
