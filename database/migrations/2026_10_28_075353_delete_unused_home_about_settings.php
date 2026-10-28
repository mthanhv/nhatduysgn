<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Home and About settings no page reads any more: the fields replaced by one rich-text block per section, and the
     * partner (JBE) block that was removed from the About page. Their English versions go too.
     */
    private const KEYS = [
        'home_hero_eyebrow', 'home_hero_title', 'home_hero_text', 'home_hero_caption_brand', 'home_hero_caption_title',
        'home_stats', 'home_about_title', 'home_about_text', 'home_about_points', 'industries',
        'about_hero_title', 'about_hero_text', 'about_intro_title', 'about_intro_body', 'about_pillars', 'about_values',
        'about_spotlight_label', 'about_spotlight_country', 'about_spotlight_title', 'about_spotlight_text',
        'about_spotlight_image', 'about_spotlight_content',
    ];

    public function up(): void
    {
        $keys = [...self::KEYS, ...array_map(fn (string $key) => "{$key}_en", self::KEYS)];

        Setting::query()->whereIn('key', $keys)->delete();
        // Setting::get() caches every row; drop the cache so removed keys are not served from it.
        Setting::putMany([]);
    }

    public function down(): void
    {
        //
    }
};
