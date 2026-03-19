<?php

namespace App\Livewire\Web\Admin;

use App\Livewire\AbstractComponent;
use App\Livewire\Web\Concerns\WithLocalizedContextTrait;
use App\Models\Allergen;
use App\Models\Ingredient;
use App\Models\Label;
use App\Models\Recipe;
use App\Models\Tag;
use App\Models\Utensil;
use Illuminate\Contracts\View\View as ViewInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('web::components.layouts.localized')]
class RecipeForm extends AbstractComponent
{
    use WithFileUploads;
    use WithLocalizedContextTrait;

    #[Locked]
    public ?int $recipeId = null;

    #[Locked]
    public ?string $existingImagePath = null;

    public ?TemporaryUploadedFile $newImage = null;

    public string $name = '';

    public string $headline = '';

    public string $description = '';

    public int $difficulty = 1;

    public ?int $prepTime = null;

    public ?int $totalTime = null;

    public bool $published = false;

    /**
     * Steps as ordered array.
     *
     * @var list<array{instructions: string, existing_image_path: string|null}>
     */
    public array $steps = [['instructions' => '', 'existing_image_path' => null]];

    /**
     * Temporary uploaded images for each step, parallel-indexed to $steps.
     *
     * @var array<int, TemporaryUploadedFile|null>
     */
    public array $stepImages = [null];

    /**
     * Available serving sizes, e.g. [2, 4].
     *
     * @var list<int>
     */
    public array $yields = [2];

    public int $newYieldSize = 4;

    /**
     * Each row: ingredient_id, display name, and amounts keyed by yield size.
     *
     * @var list<array{ingredient_id: int, name: string, amounts: array<string, array{amount: string, unit: string}>}>
     */
    public array $ingredientRows = [];

    public string $ingredientSearch = '';

    public ?int $labelId = null;

    public string $labelSearch = '';

    /** @var list<int> */
    public array $tagIds = [];

    public string $tagSearch = '';

    /** @var list<int> */
    public array $utensilIds = [];

    public string $utensilSearch = '';

    /** @var list<int> */
    public array $allergenIds = [];

    public string $allergenSearch = '';

    /**
     * Initialize for edit mode when a recipe is provided.
     */
    public function mount(?Recipe $recipe = null): void
    {
        if ($recipe instanceof Recipe) {
            abort_if($recipe->country_id !== $this->countryId, 404);
            $this->recipeId = $recipe->id;
            $this->loadFromRecipe($recipe->load(['ingredients', 'tags', 'label', 'utensils', 'allergens']));
        }
    }

    protected function loadFromRecipe(Recipe $recipe): void
    {
        $this->existingImagePath = $recipe->image_path;
        $this->name = $recipe->getTranslation('name', $this->locale) ?? '';
        $this->headline = $recipe->getTranslation('headline', $this->locale) ?? '';
        $this->description = $recipe->getTranslation('description', $this->locale) ?? '';
        $this->difficulty = $recipe->difficulty ?? 1;
        $this->prepTime = $recipe->prep_time;
        $this->totalTime = $recipe->total_time;
        $this->published = (bool) $recipe->published;

        $stepsData = $recipe->steps_primary ?? [];
        $this->steps = $stepsData !== []
            ? array_values(array_map(
                fn (array $step): array => [
                    'instructions' => $step['instructions'] ?? '',
                    'existing_image_path' => ($step['images'][0]['path'] ?? null),
                ],
                $stepsData
            ))
            : [['instructions' => '', 'existing_image_path' => null]];
        $this->stepImages = array_fill(0, count($this->steps), null);

        $yieldsData = $recipe->yields_primary ?? [];
        if ($yieldsData !== []) {
            $this->yields = array_values(array_map(
                fn (array $y): int => (int) $y['yields'],
                $yieldsData
            ));
        }

        $this->ingredientRows = $this->buildIngredientRows($recipe, $yieldsData);
        $this->labelId = $recipe->label_id;
        $this->tagIds = $recipe->tags->pluck('id')->all();
        $this->utensilIds = $recipe->utensils->pluck('id')->all();
        $this->allergenIds = $recipe->allergens->pluck('id')->all();
    }

    /**
     * Build the ingredient rows for the form from the recipe's stored data.
     *
     * @param  array<int, array<string, mixed>>  $yieldsData
     * @return list<array{ingredient_id: int, name: string, amounts: array<string, array{amount: string, unit: string}>}>
     */
    protected function buildIngredientRows(Recipe $recipe, array $yieldsData): array
    {
        // Build lookup: refId => [yieldSizeKey => ['amount' => ..., 'unit' => ...]]
        $amountsByRef = [];
        foreach ($yieldsData as $yieldData) {
            $yieldSizeKey = (string) ((int) $yieldData['yields']);
            foreach ($yieldData['ingredients'] ?? [] as $ing) {
                $refId = (string) ($ing['id'] ?? '');
                if ($refId === '') {
                    continue;
                }

                $amountsByRef[$refId][$yieldSizeKey] = [
                    'amount' => (string) ($ing['amount'] ?? ''),
                    'unit' => (string) ($ing['unit'] ?? ''),
                ];
            }
        }

        $rows = [];
        foreach ($recipe->ingredients as $ingredient) {
            $refId = $this->resolveIngredientRefId($ingredient, array_keys($amountsByRef));
            $amounts = [];
            foreach ($this->yields as $yield) {
                $amounts[(string) $yield] = $amountsByRef[$refId][(string) $yield]
                    ?? ['amount' => '', 'unit' => ''];
            }

            $rows[] = [
                'ingredient_id' => $ingredient->id,
                'name' => $ingredient->name ?: ($ingredient->getFirstTranslation('name') ?? ''),
                'amounts' => $amounts,
            ];
        }

        return $rows;
    }

    /**
     * Find the reference ID used in yields_primary for this ingredient.
     * Prefers a known hellofresh_id, falls back to db-{id}.
     *
     * @param  list<string>  $knownRefIds
     */
    protected function resolveIngredientRefId(Ingredient $ingredient, array $knownRefIds): string
    {
        /** @var list<string>|null $hellofreshIds */
        $hellofreshIds = $ingredient->hellofresh_ids;
        if ($hellofreshIds !== null) {
            foreach ($hellofreshIds as $hellofreshId) {
                if (in_array($hellofreshId, $knownRefIds, true)) {
                    return $hellofreshId;
                }
            }
        }

        return 'db-' . $ingredient->id;
    }

    /**
     * Determine the reference ID to store in yields_primary for a given ingredient.
     * Uses the first hellofresh_id if available, otherwise db-{id}.
     */
    protected function getIngredientSaveRefId(int $ingredientId): string
    {
        $ingredient = Ingredient::find($ingredientId);
        /** @var list<string>|null $hellofreshIds */
        $hellofreshIds = $ingredient?->hellofresh_ids;
        if ($hellofreshIds !== null && $hellofreshIds !== []) {
            return $hellofreshIds[0];
        }

        return 'db-' . $ingredientId;
    }

    // ── Steps ────────────────────────────────────────────────────

    public function addStep(): void
    {
        $this->steps[] = ['instructions' => '', 'existing_image_path' => null];
        $this->stepImages[] = null;
    }

    public function removeStep(int $index): void
    {
        array_splice($this->steps, $index, 1);
        $this->steps = array_values($this->steps);
        array_splice($this->stepImages, $index, 1);
        $this->stepImages = array_values($this->stepImages);
    }

    public function moveStepUp(int $index): void
    {
        if ($index <= 0 || $index >= count($this->steps)) {
            return;
        }

        [$this->steps[$index - 1], $this->steps[$index]] = [$this->steps[$index], $this->steps[$index - 1]];
        [$this->stepImages[$index - 1], $this->stepImages[$index]] = [$this->stepImages[$index], $this->stepImages[$index - 1]];
    }

    public function moveStepDown(int $index): void
    {
        $lastIndex = count($this->steps) - 1;
        if ($index < 0 || $index >= $lastIndex) {
            return;
        }

        [$this->steps[$index], $this->steps[$index + 1]] = [$this->steps[$index + 1], $this->steps[$index]];
        [$this->stepImages[$index], $this->stepImages[$index + 1]] = [$this->stepImages[$index + 1], $this->stepImages[$index]];
    }

    public function clearStepImage(int $index): void
    {
        $this->steps[$index]['existing_image_path'] = null;
        $this->stepImages[$index] = null;
    }

    // ── Yields ───────────────────────────────────────────────────

    public function addYield(): void
    {
        $servings = $this->newYieldSize;
        if (in_array($servings, $this->yields, true)) {
            return;
        }

        $this->yields[] = $servings;
        sort($this->yields);

        foreach (array_keys($this->ingredientRows) as $idx) {
            $this->ingredientRows[$idx]['amounts'][(string) $servings] = ['amount' => '', 'unit' => ''];
        }
    }

    public function removeYield(int $servings): void
    {
        if (count($this->yields) <= 1) {
            return;
        }

        $this->yields = array_values(array_filter($this->yields, fn (int $y): bool => $y !== $servings));

        foreach (array_keys($this->ingredientRows) as $idx) {
            unset($this->ingredientRows[$idx]['amounts'][(string) $servings]);
        }
    }

    // ── Ingredients ──────────────────────────────────────────────

    /**
     * Live search results for the ingredient picker.
     *
     * @return Collection<int, Ingredient>
     */
    #[Computed]
    public function ingredientSearchResults(): Collection
    {
        if (strlen($this->ingredientSearch) < 2) {
            return collect();
        }

        $existingIds = array_column($this->ingredientRows, 'ingredient_id');

        return Ingredient::where('country_id', $this->countryId)
            ->searchByName($this->locale, $this->ingredientSearch)
            ->when($existingIds !== [], fn ($q) => $q->whereNotIn('id', $existingIds))
            ->limit(8)
            ->get();
    }

    public function addIngredient(int $ingredientId): void
    {
        foreach ($this->ingredientRows as $ingredientRow) {
            if ((int) $ingredientRow['ingredient_id'] === $ingredientId) {
                return;
            }
        }

        $ingredient = Ingredient::find($ingredientId);
        if ($ingredient === null) {
            return;
        }

        $amounts = [];
        foreach ($this->yields as $yield) {
            $amounts[(string) $yield] = ['amount' => '', 'unit' => ''];
        }

        $this->ingredientRows[] = [
            'ingredient_id' => $ingredientId,
            'name' => $ingredient->name ?: ($ingredient->getFirstTranslation('name') ?? ''),
            'amounts' => $amounts,
        ];

        $this->ingredientSearch = '';
    }

    public function removeIngredient(int $index): void
    {
        array_splice($this->ingredientRows, $index, 1);
        $this->ingredientRows = array_values($this->ingredientRows);
    }

    // ── Label ────────────────────────────────────────────────────

    /**
     * Live search results for the label picker.
     *
     * @return Collection<int, Label>
     */
    #[Computed]
    public function labelSearchResults(): Collection
    {
        if (strlen($this->labelSearch) < 2) {
            return collect();
        }

        return Label::where('country_id', $this->countryId)
            ->active()
            ->searchByName($this->locale, $this->labelSearch)
            ->limit(8)
            ->get();
    }

    /**
     * The currently selected label.
     */
    #[Computed]
    public function selectedLabel(): ?Label
    {
        if ($this->labelId === null) {
            return null;
        }

        return Label::find($this->labelId);
    }

    public function setLabel(int $labelId): void
    {
        $this->labelId = $labelId;
        $this->labelSearch = '';
    }

    public function clearLabel(): void
    {
        $this->labelId = null;
    }

    // ── Tags ─────────────────────────────────────────────────────

    /**
     * Live search results for the tag picker.
     *
     * @return Collection<int, Tag>
     */
    #[Computed]
    public function tagSearchResults(): Collection
    {
        if (strlen($this->tagSearch) < 2) {
            return collect();
        }

        return Tag::where('country_id', $this->countryId)
            ->active()
            ->searchByName($this->locale, $this->tagSearch)
            ->when($this->tagIds !== [], fn ($query) => $query->whereNotIn('id', $this->tagIds))
            ->limit(8)
            ->get();
    }

    /**
     * The currently selected tags.
     *
     * @return Collection<int, Tag>
     */
    #[Computed]
    public function selectedTags(): Collection
    {
        if ($this->tagIds === []) {
            return collect();
        }

        return Tag::whereIn('id', $this->tagIds)->get();
    }

    public function addTag(int $tagId): void
    {
        if (in_array($tagId, $this->tagIds, true)) {
            return;
        }

        $this->tagIds[] = $tagId;
        $this->tagSearch = '';
    }

    public function removeTag(int $tagId): void
    {
        $this->tagIds = array_values(
            array_filter($this->tagIds, fn (int $id): bool => $id !== $tagId)
        );
    }

    public function createAndAddTag(): void
    {
        $name = trim($this->tagSearch);

        if (strlen($name) < 2) {
            return;
        }

        $tag = new Tag();
        $tag->country_id = $this->countryId;
        $tag->active = true;
        $tag->setTranslation('name', $this->locale, $name);
        $tag->save();

        $this->addTag($tag->id);
    }

    // ── Utensils ──────────────────────────────────────────────────

    /**
     * Live search results for the utensil picker.
     *
     * @return Collection<int, Utensil>
     */
    #[Computed]
    public function utensilSearchResults(): Collection
    {
        if (strlen($this->utensilSearch) < 2) {
            return collect();
        }

        return Utensil::where('country_id', $this->countryId)
            ->active()
            ->searchByName($this->locale, $this->utensilSearch)
            ->when($this->utensilIds !== [], fn ($query) => $query->whereNotIn('id', $this->utensilIds))
            ->limit(8)
            ->get();
    }

    /**
     * The currently selected utensils.
     *
     * @return Collection<int, Utensil>
     */
    #[Computed]
    public function selectedUtensils(): Collection
    {
        if ($this->utensilIds === []) {
            return collect();
        }

        return Utensil::whereIn('id', $this->utensilIds)->get();
    }

    public function addUtensil(int $utensilId): void
    {
        if (in_array($utensilId, $this->utensilIds, true)) {
            return;
        }

        $this->utensilIds[] = $utensilId;
        $this->utensilSearch = '';
    }

    public function removeUtensil(int $utensilId): void
    {
        $this->utensilIds = array_values(
            array_filter($this->utensilIds, fn (int $id): bool => $id !== $utensilId)
        );
    }

    public function createAndAddUtensil(): void
    {
        $name = trim($this->utensilSearch);

        if (strlen($name) < 2) {
            return;
        }

        $utensil = new Utensil();
        $utensil->country_id = $this->countryId;
        $utensil->active = true;
        $utensil->setTranslation('name', $this->locale, $name);
        $utensil->save();

        $this->addUtensil($utensil->id);
    }

    // ── Allergens ─────────────────────────────────────────────────

    /**
     * Live search results for the allergen picker.
     *
     * @return Collection<int, Allergen>
     */
    #[Computed]
    public function allergenSearchResults(): Collection
    {
        if (strlen($this->allergenSearch) < 2) {
            return collect();
        }

        return Allergen::where('country_id', $this->countryId)
            ->active()
            ->searchByName($this->locale, $this->allergenSearch)
            ->when($this->allergenIds !== [], fn ($query) => $query->whereNotIn('id', $this->allergenIds))
            ->limit(8)
            ->get();
    }

    /**
     * The currently selected allergens.
     *
     * @return Collection<int, Allergen>
     */
    #[Computed]
    public function selectedAllergens(): Collection
    {
        if ($this->allergenIds === []) {
            return collect();
        }

        return Allergen::whereIn('id', $this->allergenIds)->get();
    }

    public function addAllergen(int $allergenId): void
    {
        if (in_array($allergenId, $this->allergenIds, true)) {
            return;
        }

        $this->allergenIds[] = $allergenId;
        $this->allergenSearch = '';
    }

    public function removeAllergen(int $allergenId): void
    {
        $this->allergenIds = array_values(
            array_filter($this->allergenIds, fn (int $id): bool => $id !== $allergenId)
        );
    }

    // ── Save ─────────────────────────────────────────────────────

    public function save(): void
    {
        $this->validate($this->validationRules());

        $recipe = $this->recipeId !== null
            ? Recipe::findOrFail($this->recipeId)
            : new Recipe();

        if ($this->recipeId === null) {
            $recipe->country_id = $this->countryId;
            $recipe->author_id = auth()->id();
        } else {
            abort_unless((bool) auth()->user()?->admin, 403);
        }

        $this->fillRecipe($recipe);
        $recipe->save();
        $this->syncRelationships($recipe);

        $this->recipeId = $recipe->id;

        $this->redirectToRecipe($recipe);
    }

    public function createVariant(): void
    {
        abort_if($this->recipeId === null, 404);

        $this->validate($this->validationRules());

        $original = Recipe::findOrFail($this->recipeId);

        $variant = new Recipe();
        $variant->country_id = $this->countryId;
        $variant->author_id = auth()->id();
        $variant->canonical_id = $original->canonical_id ?? $original->id;
        $variant->variant = true;

        $this->fillRecipe($variant);
        $variant->save();
        $this->syncRelationships($variant);

        $this->redirectToRecipe($variant);
    }

    public function archive(): void
    {
        abort_if($this->recipeId === null, 404);
        abort_unless((bool) auth()->user()?->admin, 403);

        $recipe = Recipe::findOrFail($this->recipeId);
        $recipe->published = false;
        $recipe->save();

        $this->redirect(localized_route('localized.recipes.index'), navigate: true);
    }

    /**
     * Apply all form field values to the given recipe model.
     */
    protected function fillRecipe(Recipe $recipe): void
    {
        $recipe->setTranslation('name', $this->locale, $this->name);

        if ($this->headline !== '') {
            $recipe->setTranslation('headline', $this->locale, $this->headline);
        }

        if ($this->description !== '') {
            $recipe->setTranslation('description', $this->locale, $this->description);
        }

        $recipe->difficulty = $this->difficulty;
        $recipe->prep_time = $this->prepTime;
        $recipe->total_time = $this->totalTime;
        $recipe->published = $this->published;
        $recipe->label_id = $this->labelId;
        $recipe->steps_primary = $this->buildStepsPrimary();
        $recipe->yields_primary = $this->buildYieldsPrimary();

        if ($this->newImage instanceof TemporaryUploadedFile) {
            $filename = $this->newImage->getClientOriginalName();
            $path = $this->newImage->storeAs('hellofresh/recipes', $filename, 'public');
            $recipe->image_path = '/recipes/' . basename((string) $path);
            $this->existingImagePath = $recipe->image_path;
        } elseif ($recipe->image_path === null && $this->existingImagePath !== null) {
            $recipe->image_path = $this->existingImagePath;
        }
    }

    /**
     * Sync all pivot relationships for the given recipe.
     */
    protected function syncRelationships(Recipe $recipe): void
    {
        $recipe->ingredients()->sync(array_column($this->ingredientRows, 'ingredient_id'));
        $recipe->tags()->sync($this->tagIds);
        $recipe->utensils()->sync($this->utensilIds);
        $recipe->allergens()->sync($this->allergenIds);
    }

    /**
     * Redirect to the recipe show page.
     */
    protected function redirectToRecipe(Recipe $recipe): void
    {
        $nameForSlug = $recipe->name ?: ($recipe->getFirstTranslation('name') ?? '');
        $slug = Str::slug($nameForSlug);

        $this->redirect(
            localized_route('localized.recipes.show', [
                'slug' => $slug !== '' ? $slug : 'recipe',
                'recipe' => $recipe->id,
            ]),
            navigate: true
        );
    }

    /**
     * Returns the validation rules for the recipe form.
     *
     * @return array<string, list<mixed>>
     */
    protected function validationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'headline' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'difficulty' => ['required', 'integer', 'min:1', 'max:3'],
            'prepTime' => ['nullable', 'integer', 'min:1', 'max:600'],
            'totalTime' => ['nullable', 'integer', 'min:1', 'max:600'],
            'published' => ['boolean'],
            'newImage' => ['nullable', 'image', 'max:5120'],
            'stepImages.*' => ['nullable', 'image', 'max:5120'],
            'steps' => ['array'],
            'steps.*.instructions' => ['required', 'string'],
            'yields' => ['required', 'array', 'min:1'],
            'yields.*' => ['required', 'integer', 'min:1', 'max:20'],
            'ingredientRows' => ['array'],
            'ingredientRows.*.ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'ingredientRows.*.amounts.*.amount' => ['nullable', 'numeric', 'min:0'],
            'ingredientRows.*.amounts.*.unit' => ['nullable', 'string', 'max:50'],
            'labelId' => ['nullable', 'integer', 'exists:labels,id'],
            'tagIds' => ['array'],
            'tagIds.*' => ['integer', 'exists:tags,id'],
            'utensilIds' => ['array'],
            'utensilIds.*' => ['integer', 'exists:utensils,id'],
            'allergenIds' => ['array'],
            'allergenIds.*' => ['integer', 'exists:allergens,id'],
        ];
    }

    /**
     * Build the steps_primary JSON structure from the current form state.
     *
     * @return list<array<string, mixed>>
     */
    protected function buildStepsPrimary(): array
    {
        return array_values(array_map(
            function (int $idx, array $step): array {
                $images = [];

                if (($this->stepImages[$idx] ?? null) instanceof TemporaryUploadedFile) {
                    $file = $this->stepImages[$idx];
                    $path = $file->storeAs('hellofresh/steps', $file->getClientOriginalName(), 'public');
                    $images = [['path' => '/steps/' . basename((string) $path)]];
                } elseif (isset($step['existing_image_path']) && $step['existing_image_path'] !== null) {
                    $images = [['path' => $step['existing_image_path']]];
                }

                return [
                    'index' => $idx + 1,
                    'instructions' => $step['instructions'],
                    'images' => $images,
                ];
            },
            array_keys($this->steps),
            $this->steps
        ));
    }

    /**
     * Build the yields_primary JSON structure from the current form state.
     *
     * @return list<array<string, mixed>>
     */
    protected function buildYieldsPrimary(): array
    {
        $result = [];
        foreach ($this->yields as $yield) {
            $ingredients = [];
            foreach ($this->ingredientRows as $ingredientRow) {
                $amountData = $ingredientRow['amounts'][(string) $yield] ?? ['amount' => '', 'unit' => ''];
                $ingredients[] = [
                    'id' => $this->getIngredientSaveRefId((int) $ingredientRow['ingredient_id']),
                    'amount' => ($amountData['amount'] !== '' && $amountData['amount'] !== null)
                        ? (float) $amountData['amount']
                        : null,
                    'unit' => $amountData['unit'] ?? '',
                ];
            }

            $result[] = [
                'yields' => $yield,
                'ingredients' => $ingredients,
            ];
        }

        return $result;
    }

    public function render(): ViewInterface
    {
        $title = $this->recipeId !== null
            ? page_title(__('Edit Recipe'))
            : page_title(__('Create Recipe'));

        return view('web::livewire.admin.recipe-form')->title($title);
    }
}
