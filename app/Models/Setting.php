<?php

namespace App\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Site-wide editable content (company info, homepage, about page). Falls back to `config/site.php` defaults.
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    private const CACHE_KEY = 'site-settings';

    /**
     * Text settings with an English version stored under "{key}_en"; the English site falls back to the Vietnamese
     * value while the English one is empty.
     */
    public const TRANSLATABLE = [
        'company_name', 'tagline', 'working_hours', 'address', 'office_address', 'seo_title', 'seo_description',
        'home_hero_content', 'home_hero_caption', 'home_about_content', 'home_stats_content', 'industries_content',
        'about_hero_content', 'about_intro_content', 'about_pillars_content', 'about_origins',
        'about_values_content', 'services_hero_content', 'service_benefits_content', 'service_steps_content', 'service_faqs_content',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (app()->getLocale() === 'en' && in_array($key, self::TRANSLATABLE, true)) {
            $english = self::stored("{$key}_en");

            if (filled($english)) {
                return $english;
            }
        }

        return self::stored($key, $default);
    }

    private static function stored(string $key, mixed $default = null): mixed
    {
        $stored = Cache::rememberForever(self::CACHE_KEY, fn () => self::query()->pluck('value', 'key')->all());

        // A saved key wins even when empty, so editors can clear a field to hide that block.
        if (array_key_exists($key, $stored)) {
            return $stored[$key] ?? $default;
        }

        return config("site.{$key}", $default);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public static function putMany(array $values): void
    {
        foreach ($values as $key => $value) {
            self::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }
}
