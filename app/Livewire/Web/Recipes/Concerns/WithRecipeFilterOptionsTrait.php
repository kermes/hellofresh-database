<?php

declare(strict_types=1);

namespace App\Livewire\Web\Recipes\Concerns;

use App\Models\Allergen;
use App\Models\Ingredient;
use App\Models\Label;
use App\Models\Tag;
use App\Models\Utensil;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;

/**
 * Provides computed properties for recipe filter options.
 *
 * @property int $countryId
 * @property string $locale
 * @property string $allergenSearch
 * @property array<int> $excludedAllergenIds
 * @property array<int> $ingredientIds
 * @property array<int> $excludedIngredientIds
 * @property string $ingredientSearch
 * @property string $excludedIngredientSearch
 * @property array<int> $labelIds
 * @property string $labelSearch
 * @property array<int> $excludedLabelIds
 * @property string $excludedLabelSearch
 * @property array<int> $tagIds
 * @property array<int> $excludedTagIds
 * @property string $tagSearch
 * @property string $excludedTagSearch
 * @property array<int> $utensilIds
 * @property array<int> $excludedUtensilIds
 * @property string $utensilSearch
 * @property string $excludedUtensilSearch
 * @property Collection<int, Ingredient> $ingredientOptions
 * @property Collection<int, Ingredient> $excludedIngredientOptions
 */
trait WithRecipeFilterOptionsTrait
{
    /**
     * Get allergen options for the current country, filtered by search term.
     *
     * @return Collection<int, Allergen>
     */
    #[Computed]
    public function allergenOptions(): Collection
    {
        $query = Allergen::where('country_id', $this->countryId)
            ->active()
            ->orderBy('name');

        if ($this->allergenSearch !== '') {
            $query = $query->searchByName($this->locale, $this->allergenSearch);
        }

        return $query->get();
    }

    /**
     * Get label options (selected + search results).
     *
     * @return Collection<int, Label>
     */
    #[Computed]
    public function labelOptions(): Collection
    {
        $selected = $this->labelIds !== []
            ? Label::whereIn('id', $this->labelIds)->get()
            : new Collection();

        if ($this->labelSearch === '') {
            return $selected;
        }

        $results = Label::where('country_id', $this->countryId)
            ->active()
            ->searchByName($this->locale, $this->labelSearch)
            ->whereNotIn('id', $this->labelIds)
            ->orderBy('name->' . $this->locale)
            ->limit(20)
            ->get();

        return $selected->concat($results);
    }

    /**
     * Get excluded label options (selected + search results).
     *
     * @return Collection<int, Label>
     */
    #[Computed]
    public function excludedLabelOptions(): Collection
    {
        $selected = $this->excludedLabelIds !== []
            ? Label::whereIn('id', $this->excludedLabelIds)->get()
            : new Collection();

        if ($this->excludedLabelSearch === '') {
            return $selected;
        }

        $results = Label::where('country_id', $this->countryId)
            ->active()
            ->searchByName($this->locale, $this->excludedLabelSearch)
            ->whereNotIn('id', $this->excludedLabelIds)
            ->orderBy('name->' . $this->locale)
            ->limit(20)
            ->get();

        return $selected->concat($results);
    }

    /**
     * Get tag options (selected + search results).
     *
     * @return Collection<int, Tag>
     */
    #[Computed]
    public function tagOptions(): Collection
    {
        $selected = $this->tagIds !== []
            ? Tag::whereIn('id', $this->tagIds)->get()
            : new Collection();

        if ($this->tagSearch === '') {
            return $selected;
        }

        $results = Tag::where('country_id', $this->countryId)
            ->active()
            ->searchByName($this->locale, $this->tagSearch)
            ->whereNotIn('id', $this->tagIds)
            ->orderBy('name->' . $this->locale)
            ->limit(20)
            ->get();

        return $selected->concat($results);
    }

    /**
     * Get excluded tag options (selected + search results).
     *
     * @return Collection<int, Tag>
     */
    #[Computed]
    public function excludedTagOptions(): Collection
    {
        $selected = $this->excludedTagIds !== []
            ? Tag::whereIn('id', $this->excludedTagIds)->get()
            : new Collection();

        if ($this->excludedTagSearch === '') {
            return $selected;
        }

        $results = Tag::where('country_id', $this->countryId)
            ->active()
            ->searchByName($this->locale, $this->excludedTagSearch)
            ->whereNotIn('id', $this->excludedTagIds)
            ->orderBy('name->' . $this->locale)
            ->limit(20)
            ->get();

        return $selected->concat($results);
    }

    /**
     * Get utensil options (selected + search results).
     *
     * @return Collection<int, Utensil>
     */
    #[Computed]
    public function utensilOptions(): Collection
    {
        $selected = $this->utensilIds !== []
            ? Utensil::whereIn('id', $this->utensilIds)->get()
            : new Collection();

        if ($this->utensilSearch === '') {
            return $selected;
        }

        $results = Utensil::where('country_id', $this->countryId)
            ->active()
            ->searchByName($this->locale, $this->utensilSearch)
            ->whereNotIn('id', $this->utensilIds)
            ->orderBy('name->' . $this->locale)
            ->limit(20)
            ->get();

        return $selected->concat($results);
    }

    /**
     * Get excluded utensil options (selected + search results).
     *
     * @return Collection<int, Utensil>
     */
    #[Computed]
    public function excludedUtensilOptions(): Collection
    {
        $selected = $this->excludedUtensilIds !== []
            ? Utensil::whereIn('id', $this->excludedUtensilIds)->get()
            : new Collection();

        if ($this->excludedUtensilSearch === '') {
            return $selected;
        }

        $results = Utensil::where('country_id', $this->countryId)
            ->active()
            ->searchByName($this->locale, $this->excludedUtensilSearch)
            ->whereNotIn('id', $this->excludedUtensilIds)
            ->orderBy('name->' . $this->locale)
            ->limit(20)
            ->get();

        return $selected->concat($results);
    }

    /**
     * Get ingredient options (selected + search results).
     *
     * @return Collection<int, Ingredient>
     */
    #[Computed]
    public function ingredientOptions(): Collection
    {
        $selected = $this->ingredientIds !== []
            ? Ingredient::whereIn('id', $this->ingredientIds)->get()
            : new Collection();

        if ($this->ingredientSearch === '') {
            return $selected;
        }

        $results = Ingredient::where('country_id', $this->countryId)
            ->searchByName($this->locale, $this->ingredientSearch)
            ->whereNotIn('id', $this->ingredientIds)
            ->orderBy('name->' . $this->locale)
            ->limit(20)
            ->get();

        return $selected->concat($results);
    }

    /**
     * Get excluded ingredient options (selected + search results).
     *
     * @return Collection<int, Ingredient>
     */
    #[Computed]
    public function excludedIngredientOptions(): Collection
    {
        $selected = $this->excludedIngredientIds !== []
            ? Ingredient::whereIn('id', $this->excludedIngredientIds)->get()
            : new Collection();

        if ($this->excludedIngredientSearch === '') {
            return $selected;
        }

        $results = Ingredient::where('country_id', $this->countryId)
            ->searchByName($this->locale, $this->excludedIngredientSearch)
            ->whereNotIn('id', $this->excludedIngredientIds)
            ->orderBy('name->' . $this->locale)
            ->limit(20)
            ->get();

        return $selected->concat($results);
    }

    /**
     * Check if there are search results for ingredients (excluding selected).
     */
    public function hasIngredientSearchResults(): bool
    {
        return $this->ingredientSearch !== '' &&
            $this->ingredientOptions->count() > count($this->ingredientIds);
    }

    /**
     * Check if there are search results for excluded ingredients (excluding selected).
     */
    public function hasExcludedIngredientSearchResults(): bool
    {
        return $this->excludedIngredientSearch !== '' &&
            $this->excludedIngredientOptions->count() > count($this->excludedIngredientIds);
    }
}
