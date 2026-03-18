<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Normalizer;

/**
 * Provides accent-insensitive name search via PostgreSQL's translate() function.
 * No database extensions required.
 */
trait SearchableByNameTrait
{
    private const string ACCENT_FROM = 'áàäâãåéèëêíìïîóòöôõøúùüûñçý';

    private const string ACCENT_TO = 'aaaaaaeeeeiiiioooooouuuuncy';

    /**
     * Scope to search the translatable name field, ignoring accents and case.
     *
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function searchByName(Builder $query, string $locale, string $term): void
    {
        $normalized = static::normalizeSearchTerm($term);

        $query->whereRaw(
            'translate(lower(name->>?), ?, ?) ILIKE ?',
            [$locale, self::ACCENT_FROM, self::ACCENT_TO, '%' . $normalized . '%']
        );
    }

    /**
     * Normalize a search term: lowercase and strip combining accent marks.
     */
    protected static function normalizeSearchTerm(string $term): string
    {
        $term = mb_strtolower($term);
        $decomposed = Normalizer::normalize($term, Normalizer::FORM_D);

        if ($decomposed === false) {
            return $term;
        }

        return preg_replace('/\p{Mn}/u', '', $decomposed) ?? $term;
    }
}
