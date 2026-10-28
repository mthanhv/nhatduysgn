<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Drops the old "Đại diện chính thức tại Việt Nam" line above the partner block on the About page; a label an
     * editor typed themselves is kept.
     */
    public function up(): void
    {
        if (Setting::query()->where('key', 'about_spotlight_label')->first()?->value === 'Đại diện chính thức tại Việt Nam') {
            Setting::putMany(['about_spotlight_label' => null]);
        }
    }

    public function down(): void
    {
        //
    }
};
