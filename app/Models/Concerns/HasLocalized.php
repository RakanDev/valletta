<?php

namespace App\Models\Concerns;

trait HasLocalized
{
    /** Value of a {"ar","en"} JSON attribute in the current locale, falling back to the other. */
    public function tr(string $attribute, ?string $locale = null): string
    {
        $value = $this->{$attribute} ?? [];
        if (! is_array($value)) {
            return (string) $value;
        }
        $locale ??= app()->getLocale();
        $fallback = $locale === 'ar' ? 'en' : 'ar';

        return (string) (($value[$locale] ?? null) ?: ($value[$fallback] ?? ''));
    }

    /** @return string[] lines of a multi-line localized attribute */
    public function trLines(string $attribute): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/u', $this->tr($attribute)))));
    }
}
