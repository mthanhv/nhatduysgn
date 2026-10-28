<?php

namespace App\Models;

/**
 * Content fields with other-language versions in the `translations` JSON column ({"en": {"name": "..."}}).
 *
 * While the public site runs in another language, reading a translatable field returns its translation when an
 * editor filled one in, and the Vietnamese original otherwise. Stored attributes never change, so saving a model
 * is unaffected. Models list their fields in `translatable()`.
 */
trait HasTranslations
{
    public const DEFAULT_LOCALE = 'vi';

    /**
     * @return array<int, string>
     */
    abstract public function translatable(): array;

    public function initializeHasTranslations(): void
    {
        $this->mergeCasts(['translations' => 'array']);
    }

    public function getAttribute($key): mixed
    {
        $locale = app()->getLocale();

        if ($locale !== self::DEFAULT_LOCALE && in_array($key, $this->translatable(), true)) {
            $translation = $this->translation($key, $locale);

            if (filled($translation)) {
                return $translation;
            }
        }

        return parent::getAttribute($key);
    }

    /**
     * The field in the current language only, without falling back to Vietnamese: for SEO texts, where a missing
     * English one should be generated from the English content instead of showing the Vietnamese one.
     */
    public function ownValue(string $field): mixed
    {
        $locale = app()->getLocale();

        return $locale === self::DEFAULT_LOCALE ? parent::getAttribute($field) : $this->translation($field, $locale);
    }

    public function translation(string $field, string $locale): mixed
    {
        return $this->getAttributeValue('translations')[$locale][$field] ?? null;
    }

    /**
     * Whether the record's main field (its first translatable field: name or title) has a version in $locale.
     */
    public function isTranslatedInto(string $locale): bool
    {
        return $locale === self::DEFAULT_LOCALE || filled($this->translation($this->translatable()[0], $locale));
    }
}
