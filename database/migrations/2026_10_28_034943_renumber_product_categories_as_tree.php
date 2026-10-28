<?php

use App\Models\ProductCategory;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Brings existing groups into tree order so the admin list can be dragged as a tree.
     */
    public function up(): void
    {
        ProductCategory::renumberAsTree();
    }

    public function down(): void
    {
        //
    }
};
