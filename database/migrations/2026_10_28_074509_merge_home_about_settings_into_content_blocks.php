<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * The Home and About pages now take one rich-text block per section (App\Services\ContentBlock) instead of many
     * small fields. Blocks an editor had already saved are rebuilt from those fields, in both languages; blocks never
     * saved keep using the defaults in config/site.php. The old keys are left in place.
     */
    public function up(): void
    {
        $stored = Setting::query()->get(['key', 'value'])->pluck('value', 'key');
        $blocks = [];

        foreach (['', '_en'] as $suffix) {
            $old = fn (string $key) => $stored->get($key.$suffix);
            $has = fn (string ...$keys) => collect($keys)->contains(fn (string $key) => $stored->has($key.$suffix));
            $p = fn (?string $text) => filled($text) ? '<p>'.e($text).'</p>' : '';
            $h = fn (string $tag, ?string $text) => filled($text) ? "<{$tag}>".e($text)."</{$tag}>" : '';
            $list = fn (?array $items, Closure $item) => $items ? '<ul>'.collect($items)->map(fn (array $row) => '<li><p>'.$item($row).'</p></li>')->implode('').'</ul>' : '';
            $boldText = fn (array $row, string $bold, string $text) => '<strong>'.e($row[$bold] ?? '').'</strong>'.(filled($row[$text] ?? null) ? ' — '.e($row[$text]) : '');

            if ($has('home_hero_eyebrow', 'home_hero_title', 'home_hero_text')) {
                $blocks['home_hero_content'.$suffix] = $p($old('home_hero_eyebrow')).$h('h2', $old('home_hero_title')).$p($old('home_hero_text'));
            }
            if ($has('home_hero_caption_brand', 'home_hero_caption_title')) {
                $blocks['home_hero_caption'.$suffix] = trim(collect([$old('home_hero_caption_brand'), $old('home_hero_caption_title')])->filter()->implode("\n")) ?: null;
            }
            if ($has('home_about_title', 'home_about_text', 'home_about_points')) {
                $blocks['home_about_content'.$suffix] = $h('h2', $old('home_about_title')).$p($old('home_about_text'))
                    .$list($old('home_about_points'), fn (array $row) => $boldText($row, 'title', 'text'));
            }
            if ($has('home_stats')) {
                $blocks['home_stats_content'.$suffix] = $list($old('home_stats'), fn (array $row) => '<strong>'.e($row['value'] ?? '').'</strong> '.e($row['label'] ?? '')) ?: null;
            }
            if ($has('industries')) {
                $blocks['industries_content'.$suffix] = $list($old('industries'), fn (array $row) => e($row['name'] ?? '')) ?: null;
            }
            if ($has('about_hero_title', 'about_hero_text')) {
                $blocks['about_hero_content'.$suffix] = $h('h2', $old('about_hero_title')).$p($old('about_hero_text'));
            }
            if ($has('about_intro_title', 'about_intro_body')) {
                $blocks['about_intro_content'.$suffix] = $h('h2', $old('about_intro_title')).(string) $old('about_intro_body');
            }
            if ($has('about_pillars')) {
                $blocks['about_pillars_content'.$suffix] = collect($old('about_pillars') ?? [])
                    ->map(fn (array $row) => $h('h3', trim(($row['title'] ?? '').(filled($row['en'] ?? null) ? ' ('.$row['en'].')' : ''))).$p($row['text'] ?? null))
                    ->implode('') ?: null;
            }
            if ($has('about_values')) {
                $blocks['about_values_content'.$suffix] = collect($old('about_values') ?? [])
                    ->map(fn (array $row) => $h('h3', $row['title'] ?? null).$p($row['text'] ?? null))
                    ->implode('') ?: null;
            }
        }

        if ($blocks !== []) {
            Setting::putMany(array_map(fn (?string $html) => $html === '' ? null : $html, $blocks));
        }
    }

    public function down(): void
    {
        //
    }
};
