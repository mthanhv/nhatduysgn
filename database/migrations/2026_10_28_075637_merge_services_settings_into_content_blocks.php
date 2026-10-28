<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * The Services page now takes one rich-text block per section (App\Services\ContentBlock), like Home and About.
     * Sections an editor had saved are rebuilt in both languages (the hero gets the title the page used to fix in
     * code); the old keys are then deleted. Sections never saved keep using the defaults in config/site.php.
     */
    private const OLD_KEYS = ['services_intro', 'service_benefits', 'service_faqs', 'service_steps'];

    private const HERO = [
        '' => ['Dịch vụ kỹ thuật tại hiện trường', 'Đúng thiết bị, đúng quy trình, ngay tại nhà máy của bạn'],
        '_en' => ['On-site technical services', 'The right equipment and procedures, right at your plant'],
    ];

    public function up(): void
    {
        $stored = Setting::query()->get(['key', 'value'])->pluck('value', 'key');
        $blocks = [];

        foreach (self::HERO as $suffix => [$eyebrow, $title]) {
            $cards = fn (?array $rows, string $heading, string $text) => collect($rows ?? [])
                ->map(fn (array $row) => (filled($row[$heading] ?? null) ? '<h3>'.e($row[$heading]).'</h3>' : '').(filled($row[$text] ?? null) ? '<p>'.e($row[$text]).'</p>' : ''))
                ->implode('') ?: null;

            if ($stored->has('services_intro'.$suffix)) {
                $blocks['services_hero_content'.$suffix] = '<p>'.e($eyebrow).'</p><h2>'.e($title).'</h2>'
                    .(filled($stored->get('services_intro'.$suffix)) ? '<p>'.e($stored->get('services_intro'.$suffix)).'</p>' : '');
            }
            if ($stored->has('service_benefits'.$suffix)) {
                $blocks['service_benefits_content'.$suffix] = $cards($stored->get('service_benefits'.$suffix), 'title', 'text');
            }
            if ($stored->has('service_steps'.$suffix)) {
                $blocks['service_steps_content'.$suffix] = $cards($stored->get('service_steps'.$suffix), 'title', 'text');
            }
            if ($stored->has('service_faqs'.$suffix)) {
                $blocks['service_faqs_content'.$suffix] = $cards($stored->get('service_faqs'.$suffix), 'question', 'answer');
            }
        }

        if ($blocks !== []) {
            Setting::putMany($blocks);
        }

        Setting::query()->whereIn('key', [...self::OLD_KEYS, ...array_map(fn (string $key) => "{$key}_en", self::OLD_KEYS)])->delete();
        // Setting::get() caches every row; drop the cache so removed keys are not served from it.
        Setting::putMany([]);
    }

    public function down(): void
    {
        //
    }
};
