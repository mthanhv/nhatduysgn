<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Hides the JBE partner block on the About page (it shows only while it has a title); a block an editor
     * re-titled themselves is kept.
     */
    public function up(): void
    {
        if (Setting::query()->where('key', 'about_spotlight_title')->first()?->value === 'JBE — thiết bị ngành ống thép & lò hơi') {
            Setting::putMany(['about_spotlight_title' => null]);
        }
    }

    public function down(): void
    {
        //
    }
};
