<div>
<main class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-section">

  {{-- Page header --}}
  <div class="flex items-center justify-between mb-section">
    <flux:heading size="xl">
      {{ $recipeId ? __('Edit Recipe') : __('Create Recipe') }}
    </flux:heading>
    @if ($recipeId)
      <flux:button
        :href="localized_route('localized.recipes.show', ['slug' => 'recipe', 'recipe' => $recipeId])"
        variant="ghost"
        icon="arrow-left"
        wire:navigate
      >
        {{ __('Back to Recipe') }}
      </flux:button>
    @else
      <flux:button
        :href="localized_route('localized.recipes.index')"
        variant="ghost"
        icon="arrow-left"
        wire:navigate
      >
        {{ __('Back to Recipes') }}
      </flux:button>
    @endif
  </div>

  <form wire:submit="save" class="space-y-section">

    {{-- ── Basic Information ── --}}
    <flux:card class="space-y-4">
      <flux:heading size="lg">{{ __('Basic Information') }}</flux:heading>

      <div>
        <flux:input
          wire:model="name"
          :label="__('Name')"
          required
        />
        @error('name')
          <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <flux:input
          wire:model="headline"
          :label="__('Headline')"
          :placeholder="__('Short one-liner description')"
        />
        @error('headline')
          <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <flux:textarea
          wire:model="description"
          :label="__('Description')"
          rows="3"
        />
        @error('description')
          <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <flux:select wire:model="difficulty" :label="__('Difficulty')">
            <option value="1">{{ __('Easy') }}</option>
            <option value="2">{{ __('Medium') }}</option>
            <option value="3">{{ __('Hard') }}</option>
          </flux:select>
          @error('difficulty')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <flux:input
            wire:model="prepTime"
            type="number"
            :label="__('Prep Time (min)')"
            min="1"
            max="600"
          />
          @error('prepTime')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <flux:input
            wire:model="totalTime"
            type="number"
            :label="__('Total Time (min)')"
            min="1"
            max="600"
          />
          @error('totalTime')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
          @enderror
        </div>
      </div>

      <flux:checkbox wire:model="published" :label="__('Published')" />

      <div>
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-ui">{{ __('Image') }}</label>
        <div class="flex items-center gap-4">
          @if ($newImage)
            <img src="{{ $newImage->temporaryUrl() }}" alt="" class="size-24 rounded-lg object-cover shrink-0">
          @elseif ($existingImagePath)
            <img src="{{ \App\Support\HelloFresh\HelloFreshAsset::recipeCard($existingImagePath) }}" alt="" class="size-24 rounded-lg object-cover shrink-0">
          @endif
          <label class="cursor-pointer inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 transition">
            <flux:icon.image class="size-4" />
            {{ $newImage || $existingImagePath ? __('Change Image') : __('Upload Image') }}
            <input type="file" wire:model="newImage" accept="image/*" class="sr-only">
          </label>
        </div>
        @error('newImage')
          <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
      </div>
    </flux:card>

    {{-- ── Tags ── --}}
    <flux:card class="space-y-4">
      <flux:heading size="lg">{{ __('Tags') }}</flux:heading>

      @if ($this->selectedTags->isNotEmpty())
        <div class="flex flex-wrap gap-ui">
          @foreach ($this->selectedTags as $tag)
            <div wire:key="selected-tag-{{ $tag->id }}" class="inline-flex items-center gap-1.5 bg-zinc-800 dark:bg-zinc-200 text-zinc-100 dark:text-zinc-900 rounded-full px-3 py-1 text-sm font-medium">
              <span class="size-1.5 rounded-full bg-current opacity-60 shrink-0"></span>
              <span>{{ $tag->name }}</span>
              <button type="button" wire:click="removeTag({{ $tag->id }})" class="ml-0.5 opacity-50 hover:opacity-100 transition-opacity">
                <flux:icon.x class="size-3" />
              </button>
            </div>
          @endforeach
        </div>
      @endif

      <div x-data="{ open: false }" class="relative">
        <flux:input
          wire:model.live.debounce.300ms="tagSearch"
          :placeholder="__('Search tags...')"
          icon="search"
          x-on:focus="open = true"
          x-on:click.outside="open = false"
        />

        @if (strlen($tagSearch) >= 2)
          <div
            x-show="open"
            class="absolute z-10 w-full mt-1 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 shadow-lg overflow-hidden"
          >
            @foreach ($this->tagSearchResults as $tag)
              <button
                wire:key="tag-result-{{ $tag->id }}"
                type="button"
                wire:click="addTag({{ $tag->id }})"
                x-on:click="open = false"
                class="w-full px-3 py-2 text-sm text-left hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors"
              >
                {{ $tag->name }}
              </button>
            @endforeach
            <button
              type="button"
              wire:click="createAndAddTag"
              x-on:click="open = false"
              class="w-full flex items-center gap-2 px-3 py-2 text-sm text-left text-lime-700 dark:text-lime-400 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors {{ $this->tagSearchResults->isNotEmpty() ? 'border-t border-zinc-100 dark:border-zinc-700' : '' }}"
            >
              <flux:icon.plus class="size-3.5 shrink-0" />
              {{ __('Create ":name"', ['name' => $tagSearch]) }}
            </button>
          </div>
        @endif
      </div>
    </flux:card>

    {{-- ── Utensils ── --}}
    <flux:card class="space-y-4">
      <flux:heading size="lg">{{ __('Utensils') }}</flux:heading>

      @if ($this->selectedUtensils->isNotEmpty())
        <div class="flex flex-wrap gap-ui">
          @foreach ($this->selectedUtensils as $utensil)
            <div wire:key="selected-utensil-{{ $utensil->id }}" class="inline-flex items-center gap-1.5 bg-zinc-800 dark:bg-zinc-200 text-zinc-100 dark:text-zinc-900 rounded-full px-3 py-1 text-sm font-medium">
              <span class="size-1.5 rounded-full bg-current opacity-60 shrink-0"></span>
              <span>{{ $utensil->name }}</span>
              <button type="button" wire:click="removeUtensil({{ $utensil->id }})" class="ml-0.5 opacity-50 hover:opacity-100 transition-opacity">
                <flux:icon.x class="size-3" />
              </button>
            </div>
          @endforeach
        </div>
      @endif

      <div x-data="{ open: false }" class="relative">
        <flux:input
          wire:model.live.debounce.300ms="utensilSearch"
          :placeholder="__('Search utensils...')"
          icon="search"
          x-on:focus="open = true"
          x-on:click.outside="open = false"
        />

        @if (strlen($utensilSearch) >= 2)
          <div
            x-show="open"
            class="absolute z-10 w-full mt-1 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 shadow-lg overflow-hidden"
          >
            @foreach ($this->utensilSearchResults as $utensil)
              <button
                wire:key="utensil-result-{{ $utensil->id }}"
                type="button"
                wire:click="addUtensil({{ $utensil->id }})"
                x-on:click="open = false"
                class="w-full px-3 py-2 text-sm text-left hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors"
              >
                {{ $utensil->name }}
              </button>
            @endforeach
            <button
              type="button"
              wire:click="createAndAddUtensil"
              x-on:click="open = false"
              class="w-full flex items-center gap-2 px-3 py-2 text-sm text-left text-lime-700 dark:text-lime-400 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors {{ $this->utensilSearchResults->isNotEmpty() ? 'border-t border-zinc-100 dark:border-zinc-700' : '' }}"
            >
              <flux:icon.plus class="size-3.5 shrink-0" />
              {{ __('Create ":name"', ['name' => $utensilSearch]) }}
            </button>
          </div>
        @endif
      </div>
    </flux:card>

    {{-- ── Allergens ── --}}
    <flux:card class="space-y-4">
      <flux:heading size="lg">{{ __('Allergens') }}</flux:heading>

      @if ($this->selectedAllergens->isNotEmpty())
        <div class="flex flex-wrap gap-ui">
          @foreach ($this->selectedAllergens as $allergen)
            <div wire:key="selected-allergen-{{ $allergen->id }}" class="inline-flex items-center gap-1.5 bg-zinc-800 dark:bg-zinc-200 text-zinc-100 dark:text-zinc-900 rounded-full px-3 py-1 text-sm font-medium">
              <span class="size-1.5 rounded-full bg-current opacity-60 shrink-0"></span>
              <span>{{ $allergen->name }}</span>
              <button type="button" wire:click="removeAllergen({{ $allergen->id }})" class="ml-0.5 opacity-50 hover:opacity-100 transition-opacity">
                <flux:icon.x class="size-3" />
              </button>
            </div>
          @endforeach
        </div>
      @endif

      <div x-data="{ open: false }" class="relative">
        <flux:input
          wire:model.live.debounce.300ms="allergenSearch"
          :placeholder="__('Search allergens...')"
          icon="search"
          x-on:focus="open = true"
          x-on:click.outside="open = false"
        />

        @if (strlen($allergenSearch) >= 2)
          <div
            x-show="open"
            class="absolute z-10 w-full mt-1 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 shadow-lg overflow-hidden"
          >
            @forelse ($this->allergenSearchResults as $allergen)
              <button
                wire:key="allergen-result-{{ $allergen->id }}"
                type="button"
                wire:click="addAllergen({{ $allergen->id }})"
                x-on:click="open = false"
                class="w-full px-3 py-2 text-sm text-left hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors"
              >
                {{ $allergen->name }}
              </button>
            @empty
              <p class="px-3 py-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No allergens found.') }}</p>
            @endforelse
          </div>
        @endif
      </div>
    </flux:card>

    {{-- ── Label ── --}}
    <flux:card class="space-y-4">
      <flux:heading size="lg">{{ __('Label') }}</flux:heading>

      @if ($this->selectedLabel)
        <div class="inline-flex items-center gap-ui">
          <span
            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-medium"
            style="color: {{ $this->selectedLabel->foreground_color ?? '#ffffff' }}; background-color: {{ $this->selectedLabel->background_color ?? '#3f3f46' }};"
          >
            {{ $this->selectedLabel->name }}
          </span>
          <button type="button" wire:click="clearLabel" class="text-zinc-400 hover:text-red-500 transition-colors">
            <flux:icon.x class="size-4" />
          </button>
        </div>
      @endif

      <div x-data="{ open: false }" class="relative">
        <flux:input
          wire:model.live.debounce.300ms="labelSearch"
          :placeholder="__('Search labels...')"
          icon="search"
          x-on:focus="open = true"
          x-on:click.outside="open = false"
        />

        @if (strlen($labelSearch) >= 2)
          <div
            x-show="open"
            class="absolute z-10 w-full mt-1 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 shadow-lg overflow-hidden"
          >
            @forelse ($this->labelSearchResults as $label)
              <button
                wire:key="label-result-{{ $label->id }}"
                type="button"
                wire:click="setLabel({{ $label->id }})"
                x-on:click="open = false"
                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-left hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors"
              >
                <span
                  class="inline-block size-3 rounded-full shrink-0"
                  style="background-color: {{ $label->background_color ?? '#3f3f46' }};"
                ></span>
                {{ $label->name }}
              </button>
            @empty
              <p class="px-3 py-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No labels found.') }}</p>
            @endforelse
          </div>
        @endif
      </div>
    </flux:card>

    {{-- ── Preparation Steps ── --}}
    <flux:card class="space-y-4">
      <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Preparation Steps') }}</flux:heading>
        <flux:button wire:click="addStep" variant="primary" icon="plus" size="sm">
          {{ __('Add Step') }}
        </flux:button>
      </div>

      @if (empty($steps))
        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No steps yet. Add the first step above.') }}</p>
      @endif

      @foreach ($steps as $stepIndex => $step)
        <div wire:key="step-{{ $stepIndex }}" class="flex gap-3">
          {{-- Step number --}}
          <div class="shrink-0 size-8 rounded-full bg-lime-500 text-white flex items-center justify-center font-semibold text-sm mt-1">
            {{ $stepIndex + 1 }}
          </div>

          {{-- Instructions + image --}}
          <div class="flex-1 space-y-ui">
            <x-web.html-editor
              :wire-model="'steps.' . $stepIndex . '.instructions'"
              :value="$steps[$stepIndex]['instructions'] ?? ''"
              :placeholder="__('Describe this step...')"
            />
            @error('steps.' . $stepIndex . '.instructions')
              <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            <div class="flex items-center gap-ui">
              @if ($stepImages[$stepIndex] ?? null)
                <img src="{{ $stepImages[$stepIndex]->temporaryUrl() }}" alt="" class="size-16 rounded object-cover shrink-0">
                <flux:button type="button" wire:click="clearStepImage({{ $stepIndex }})" variant="ghost" square size="sm" class="text-red-500 hover:text-red-600">
                  <flux:icon.x class="size-4" />
                </flux:button>
              @elseif ($steps[$stepIndex]['existing_image_path'] ?? null)
                <img src="{{ \App\Support\HelloFresh\HelloFreshAsset::stepImage($steps[$stepIndex]['existing_image_path']) }}" alt="" class="size-16 rounded object-cover shrink-0">
                <flux:button type="button" wire:click="clearStepImage({{ $stepIndex }})" variant="ghost" square size="sm" class="text-red-500 hover:text-red-600">
                  <flux:icon.x class="size-4" />
                </flux:button>
              @endif
              <label class="cursor-pointer inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 transition">
                <flux:icon.image class="size-4" />
                {{ ($stepImages[$stepIndex] ?? null) || ($steps[$stepIndex]['existing_image_path'] ?? null) ? __('Change') : __('Add Image') }}
                <input type="file" wire:model="stepImages.{{ $stepIndex }}" accept="image/*" class="sr-only">
              </label>
              @error('stepImages.' . $stepIndex)
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
              @enderror
            </div>
          </div>

          {{-- Move + remove controls --}}
          <div class="shrink-0 flex flex-col gap-1 mt-1">
            <flux:button
              wire:click="moveStepUp({{ $stepIndex }})"
              variant="ghost"
              square
              size="sm"
              :disabled="$stepIndex === 0"
            >
              <flux:icon.chevron-up class="size-4" />
            </flux:button>
            <flux:button
              wire:click="moveStepDown({{ $stepIndex }})"
              variant="ghost"
              square
              size="sm"
              :disabled="$stepIndex === count($steps) - 1"
            >
              <flux:icon.chevron-down class="size-4" />
            </flux:button>
            <flux:button
              wire:click="removeStep({{ $stepIndex }})"
              variant="ghost"
              square
              size="sm"
              class="text-red-500 hover:text-red-600"
            >
              <flux:icon.trash class="size-4" />
            </flux:button>
          </div>
        </div>
      @endforeach
    </flux:card>

    {{-- ── Servings & Ingredients ── --}}
    <flux:card class="space-y-4">
      <flux:heading size="lg">{{ __('Ingredients') }}</flux:heading>

      {{-- Yield size management --}}
      <div>
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-ui">
          {{ __('Serving Sizes') }}
        </label>
        <div class="flex flex-wrap items-center gap-ui">
          @foreach ($yields as $yieldSize)
            <div wire:key="yield-{{ $yieldSize }}" class="inline-flex items-center gap-1 bg-zinc-100 dark:bg-zinc-700 rounded-full px-3 py-1 text-sm">
              <span>{{ $yieldSize }} {{ __('servings') }}</span>
              @if (count($yields) > 1)
                <button
                  type="button"
                  wire:click="removeYield({{ $yieldSize }})"
                  class="ml-1 text-zinc-400 hover:text-red-500 transition-colors"
                >
                  <flux:icon.x class="size-3" />
                </button>
              @endif
            </div>
          @endforeach

          {{-- Add new yield --}}
          <div class="flex items-center gap-ui">
            <flux:input
              wire:model="newYieldSize"
              type="number"
              min="1"
              max="20"
              class="w-20"
              size="sm"
            />
            <flux:button wire:click="addYield" variant="outline" size="sm" icon="plus">
              {{ __('Add size') }}
            </flux:button>
          </div>
        </div>
      </div>

      {{-- Ingredient search --}}
      <div x-data="{ open: false }" class="relative">
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-ui">
          {{ __('Add Ingredient') }}
        </label>
        <flux:input
          wire:model.live.debounce.300ms="ingredientSearch"
          :placeholder="__('Search ingredients by name...')"
          icon="search"
          x-on:focus="open = true"
          x-on:click.outside="open = false"
        />

        @if ($this->ingredientSearchResults->isNotEmpty())
          <div
            x-show="open"
            class="absolute z-10 w-full mt-1 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 shadow-lg overflow-hidden"
          >
            @foreach ($this->ingredientSearchResults as $ingredient)
              <button
                wire:key="result-{{ $ingredient->id }}"
                type="button"
                wire:click="addIngredient({{ $ingredient->id }})"
                x-on:click="open = false"
                class="w-full flex items-center gap-3 px-3 py-2 text-sm text-left hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors"
              >
                @if ($ingredient->image_path)
                  <img
                    src="{{ \App\Support\HelloFresh\HelloFreshAsset::ingredientThumbnail($ingredient->image_path) }}"
                    alt=""
                    class="size-8 rounded object-cover shrink-0"
                  >
                @else
                  <div class="size-8 rounded bg-zinc-100 dark:bg-zinc-700 shrink-0"></div>
                @endif
                <span>{{ $ingredient->name }}</span>
              </button>
            @endforeach
          </div>
        @elseif (strlen($ingredientSearch) >= 2 && $this->ingredientSearchResults->isEmpty())
          <div class="absolute z-10 w-full mt-1 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 shadow-lg px-3 py-2 text-sm text-zinc-500 dark:text-zinc-400">
            {{ __('No ingredients found. You can add new ones in the') }}
            <a
              href="{{ localized_route('localized.admin.ingredients') }}"
              class="text-lime-600 hover:underline"
              wire:navigate
            >{{ __('ingredient manager') }}</a>.
          </div>
        @endif
      </div>

      {{-- Ingredients table --}}
      @if (!empty($ingredientRows))
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-zinc-200 dark:border-zinc-700">
                <th class="text-left py-2 pr-4 font-medium text-zinc-700 dark:text-zinc-300">
                  {{ __('Ingredient') }}
                </th>
                @foreach ($yields as $yieldSize)
                  <th colspan="2" class="text-center py-2 px-2 font-medium text-zinc-700 dark:text-zinc-300">
                    {{ $yieldSize }} {{ __('servings') }}
                  </th>
                @endforeach
                <th class="w-8"></th>
              </tr>
              <tr class="border-b border-zinc-200 dark:border-zinc-700 text-xs text-zinc-500">
                <th></th>
                @foreach ($yields as $yieldSize)
                  <th class="text-left py-1 px-2">{{ __('Amount') }}</th>
                  <th class="text-left py-1 px-2">{{ __('Unit') }}</th>
                @endforeach
                <th></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
              @foreach ($ingredientRows as $rowIndex => $row)
                <tr wire:key="ingredient-row-{{ $rowIndex }}">
                  <td class="py-2 pr-4 font-medium text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                    {{ $row['name'] }}
                  </td>
                  @foreach ($yields as $yieldSize)
                    <td class="py-2 px-2">
                      <flux:input
                        wire:model="ingredientRows.{{ $rowIndex }}.amounts.{{ $yieldSize }}.amount"
                        type="number"
                        min="0"
                        step="0.1"
                        class="w-24"
                        size="sm"
                      />
                    </td>
                    <td class="py-2 px-2">
                      <flux:input
                        wire:model="ingredientRows.{{ $rowIndex }}.amounts.{{ $yieldSize }}.unit"
                        :placeholder="__('g, ml...')"
                        class="w-24"
                        size="sm"
                      />
                    </td>
                  @endforeach
                  <td class="py-2 pl-2">
                    <flux:button
                      wire:click="removeIngredient({{ $rowIndex }})"
                      variant="ghost"
                      square
                      size="sm"
                      class="text-red-500 hover:text-red-600"
                    >
                      <flux:icon.trash class="size-4" />
                    </flux:button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <p class="text-sm text-zinc-500 dark:text-zinc-400">
          {{ __('No ingredients added yet. Search for ingredients above.') }}
        </p>
      @endif
    </flux:card>

    {{-- ── Actions ── --}}
    <div class="flex items-center gap-ui">
      @if ($recipeId && auth()->user()->admin)
        <flux:button
          wire:click="archive"
          wire:confirm="{{ __('Are you sure you want to archive this recipe? It will be hidden from the recipe list.') }}"
          variant="danger"
          icon="archive"
        >
          {{ __('Archive Recipe') }}
        </flux:button>
      @endif

      <div class="grow"></div>

      @if ($recipeId)
        <flux:button
          :href="localized_route('localized.recipes.show', ['slug' => 'recipe', 'recipe' => $recipeId])"
          variant="ghost"
          wire:navigate
        >
          {{ __('Cancel') }}
        </flux:button>
      @else
        <flux:button
          :href="localized_route('localized.recipes.index')"
          variant="ghost"
          wire:navigate
        >
          {{ __('Cancel') }}
        </flux:button>
      @endif

      @if ($recipeId)
        <flux:button
          wire:click="createVariant"
          wire:confirm="{{ __('This will create a new recipe based on your current edits. The original recipe will not be changed.') }}"
          variant="outline"
          icon="copy"
        >
          {{ __('Create Variant') }}
        </flux:button>
      @endif

      @if (!$recipeId || auth()->user()->admin)
        <flux:button type="submit" variant="primary" icon="save">
          {{ __('Save Recipe') }}
        </flux:button>
      @endif
    </div>

  </form>
</main>
</div>
