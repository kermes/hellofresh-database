<flux:modal name="recipe-filters" variant="flyout" class="space-y-section">
  <div>
    <flux:heading size="lg">{{ __('Filter Recipes') }}</flux:heading>
  </div>

  <flux:switch wire:model.live="filterHasPdf" :label="__('Only with PDF')" />

  <flux:switch wire:model.live="filterOnlyPublished" :label="__('Hide Archived')" />

  <flux:field>
    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Difficulty') }}</label>

    <flux:checkbox.group wire:model.live="difficultyLevels">
      @foreach (\App\Enums\DifficultyEnum::cases() as $difficulty)
        <flux:checkbox :value="$difficulty->value" :label="$difficulty->label()" />
      @endforeach
    </flux:checkbox.group>
  </flux:field>

  @if ($this->country()->prep_min !== null && $this->country()->prep_max !== null)
    <flux:field>
      <div class="flex items-center justify-between mb-1">
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Prep Time') }}</label>
      </div>
      <div class="flex items-center gap-2">
        <flux:input type="number" wire:model.live="prepTimeRange.0" min="0" :max="ceil($this->country()->prep_max / 10) * 10" size="sm" class="w-24" />
        <span class="text-zinc-500 shrink-0">&ndash;</span>
        <flux:input type="number" wire:model.live="prepTimeRange.1" min="0" :max="ceil($this->country()->prep_max / 10) * 10" size="sm" class="w-24" />
        <span class="text-sm text-zinc-500 shrink-0">{{ __('min') }}</span>
      </div>
    </flux:field>
  @endif

  @if ($this->country()->total_min !== null && $this->country()->total_max !== null)
    <flux:field>
      <div class="flex items-center justify-between mb-1">
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Total Time') }}</label>
      </div>
      <div class="flex items-center gap-2">
        <flux:input type="number" wire:model.live="totalTimeRange.0" min="0" :max="ceil($this->country()->total_max / 10) * 10" size="sm" class="w-24" />
        <span class="text-zinc-500 shrink-0">&ndash;</span>
        <flux:input type="number" wire:model.live="totalTimeRange.1" min="0" :max="ceil($this->country()->total_max / 10) * 10" size="sm" class="w-24" />
        <span class="text-sm text-zinc-500 shrink-0">{{ __('min') }}</span>
      </div>
    </flux:field>
  @endif

  <div class="space-y-3">
    <flux:field>
      <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('With Ingredients') }}</label>

      <flux:radio.group wire:model.live="ingredientMatchMode" variant="segmented" size="sm">
        <flux:radio :value="\App\Enums\IngredientMatchModeEnum::Any->value" :label="__('Any of')" />
        <flux:radio :value="\App\Enums\IngredientMatchModeEnum::All->value" :label="__('All of')" />
      </flux:radio.group>
    </flux:field>

    <flux:input wire:model.live.debounce.300ms="ingredientSearch" :placeholder="__('Search ingredients...')" size="sm" clearable />
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
      <div class="max-h-48 overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-800">
        @forelse ($this->ingredientOptions as $ingredient)
          <label wire:key="ingredient-{{ $ingredient->id }}" class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 cursor-pointer">
            <input type="checkbox" wire:model.live="ingredientIds" value="{{ $ingredient->id }}" class="rounded border-zinc-300 dark:border-zinc-600">
            {{ $ingredient->name }}
          </label>
        @empty
          <p class="px-3 py-2 text-sm text-zinc-500">{{ $ingredientSearch !== '' ? __('No ingredients found.') : __('Type to search...') }}</p>
        @endforelse
      </div>
    </div>
  </div>

  <flux:field>
    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Without Ingredients') }}</label>
    <flux:input wire:model.live.debounce.300ms="excludedIngredientSearch" :placeholder="__('Search ingredients...')" size="sm" clearable />
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
      <div class="max-h-48 overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-800">
        @forelse ($this->excludedIngredientOptions as $ingredient)
          <label wire:key="excluded-ingredient-{{ $ingredient->id }}" class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 cursor-pointer">
            <input type="checkbox" wire:model.live="excludedIngredientIds" value="{{ $ingredient->id }}" class="rounded border-zinc-300 dark:border-zinc-600">
            {{ $ingredient->name }}
          </label>
        @empty
          <p class="px-3 py-2 text-sm text-zinc-500">{{ __('Type to search...') }}</p>
        @endforelse
      </div>
    </div>
  </flux:field>

  <flux:field>
    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('With Labels') }}</label>
    <flux:input wire:model.live.debounce.300ms="labelSearch" :placeholder="__('Search labels...')" size="sm" clearable />
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
      <div class="max-h-48 overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-800">
        @forelse ($this->labelOptions as $label)
          <label wire:key="label-{{ $label->id }}" class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 cursor-pointer">
            <input type="checkbox" wire:model.live="labelIds" value="{{ $label->id }}" class="rounded border-zinc-300 dark:border-zinc-600">
            <span
              class="inline-block size-2.5 rounded-full shrink-0"
              style="background-color: {{ $label->background_color ?? '#3f3f46' }};"
            ></span>
            {{ $label->name }}
          </label>
        @empty
          <p class="px-3 py-2 text-sm text-zinc-500">{{ $labelSearch !== '' ? __('No labels found.') : __('Type to search...') }}</p>
        @endforelse
      </div>
    </div>
  </flux:field>

  <flux:field>
    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Without Labels') }}</label>
    <flux:input wire:model.live.debounce.300ms="excludedLabelSearch" :placeholder="__('Search labels...')" size="sm" clearable />
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
      <div class="max-h-48 overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-800">
        @forelse ($this->excludedLabelOptions as $label)
          <label wire:key="excluded-label-{{ $label->id }}" class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 cursor-pointer">
            <input type="checkbox" wire:model.live="excludedLabelIds" value="{{ $label->id }}" class="rounded border-zinc-300 dark:border-zinc-600">
            <span
              class="inline-block size-2.5 rounded-full shrink-0"
              style="background-color: {{ $label->background_color ?? '#3f3f46' }};"
            ></span>
            {{ $label->name }}
          </label>
        @empty
          <p class="px-3 py-2 text-sm text-zinc-500">{{ $excludedLabelSearch !== '' ? __('No labels found.') : __('Type to search...') }}</p>
        @endforelse
      </div>
    </div>
  </flux:field>

  <div class="space-y-3">
    <flux:field>
      <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('With Tags') }}</label>
    </flux:field>

    <flux:input wire:model.live.debounce.300ms="tagSearch" :placeholder="__('Search tags...')" size="sm" clearable />
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
      <div class="max-h-48 overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-800">
        @forelse ($this->tagOptions as $tag)
          <label wire:key="tag-{{ $tag->id }}" class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 cursor-pointer">
            <input type="checkbox" wire:model.live="tagIds" value="{{ $tag->id }}" class="rounded border-zinc-300 dark:border-zinc-600">
            {{ $tag->name }}
          </label>
        @empty
          <p class="px-3 py-2 text-sm text-zinc-500">{{ $tagSearch !== '' ? __('No tags found.') : __('Type to search...') }}</p>
        @endforelse
      </div>
    </div>
  </div>

  <flux:field>
    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Without Tags') }}</label>
    <flux:input wire:model.live.debounce.300ms="excludedTagSearch" :placeholder="__('Search tags...')" size="sm" clearable />
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
      <div class="max-h-48 overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-800">
        @forelse ($this->excludedTagOptions as $tag)
          <label wire:key="excluded-tag-{{ $tag->id }}" class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 cursor-pointer">
            <input type="checkbox" wire:model.live="excludedTagIds" value="{{ $tag->id }}" class="rounded border-zinc-300 dark:border-zinc-600">
            {{ $tag->name }}
          </label>
        @empty
          <p class="px-3 py-2 text-sm text-zinc-500">{{ __('Type to search...') }}</p>
        @endforelse
      </div>
    </div>
  </flux:field>

  <div class="space-y-3">
    <flux:field>
      <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('With Utensils') }}</label>
    </flux:field>

    <flux:input wire:model.live.debounce.300ms="utensilSearch" :placeholder="__('Search utensils...')" size="sm" clearable />
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
      <div class="max-h-48 overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-800">
        @forelse ($this->utensilOptions as $utensil)
          <label wire:key="utensil-{{ $utensil->id }}" class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 cursor-pointer">
            <input type="checkbox" wire:model.live="utensilIds" value="{{ $utensil->id }}" class="rounded border-zinc-300 dark:border-zinc-600">
            {{ $utensil->name }}
          </label>
        @empty
          <p class="px-3 py-2 text-sm text-zinc-500">{{ $utensilSearch !== '' ? __('No utensils found.') : __('Type to search...') }}</p>
        @endforelse
      </div>
    </div>
  </div>

  <flux:field>
    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Without Utensils') }}</label>
    <flux:input wire:model.live.debounce.300ms="excludedUtensilSearch" :placeholder="__('Search utensils...')" size="sm" clearable />
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
      <div class="max-h-48 overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-800">
        @forelse ($this->excludedUtensilOptions as $utensil)
          <label wire:key="excluded-utensil-{{ $utensil->id }}" class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 cursor-pointer">
            <input type="checkbox" wire:model.live="excludedUtensilIds" value="{{ $utensil->id }}" class="rounded border-zinc-300 dark:border-zinc-600">
            {{ $utensil->name }}
          </label>
        @empty
          <p class="px-3 py-2 text-sm text-zinc-500">{{ __('Type to search...') }}</p>
        @endforelse
      </div>
    </div>
  </flux:field>

  @if ($this->allergenOptions->isNotEmpty() || $allergenSearch !== '')
    <flux:field>
      <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Exclude Allergens') }}</label>
      <flux:input wire:model.live.debounce.300ms="allergenSearch" :placeholder="__('Search allergens...')" size="sm" clearable />
      <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <div class="max-h-48 overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-800">
          @forelse ($this->allergenOptions as $allergen)
            <label wire:key="allergen-{{ $allergen->id }}" class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 cursor-pointer">
              <input type="checkbox" wire:model.live="excludedAllergenIds" value="{{ $allergen->id }}" class="rounded border-zinc-300 dark:border-zinc-600">
              {{ $allergen->name }}
            </label>
          @empty
            <p class="px-3 py-2 text-sm text-zinc-500">{{ __('No allergens found.') }}</p>
          @endforelse
        </div>
      </div>
    </flux:field>
  @endif

  @if ($this->activeFilterCount > 0)
    <flux:button wire:click="clearFilters" variant="danger" class="w-full">
      {{ __('Clear All Filters') }}
    </flux:button>
  @endif
</flux:modal>
