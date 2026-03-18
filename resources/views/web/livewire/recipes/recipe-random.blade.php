<main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-section">
  <div class="flex flex-col gap-ui sm:flex-row sm:items-center sm:justify-between">
    <flux:heading size="xl" class="hidden sm:block">{{ __('Random Recipes') }}</flux:heading>

    {{-- Desktop: Controls --}}
    <div class="hidden sm:flex items-center gap-4">
      <flux:button wire:click="shuffle" icon="shuffle" size="sm">
        {{ __('Shuffle') }}
      </flux:button>

      <flux:modal.trigger name="recipe-filters">
        <flux:button icon="sliders-horizontal" size="sm">
          {{ __('Filter') }}
          @if ($this->activeFilterCount > 0)
            <flux:badge size="sm" color="lime" inset="right">{{ $this->activeFilterCount }}</flux:badge>
          @endif
        </flux:button>
      </flux:modal.trigger>

      <div class="inline-flex shrink-0 rounded-lg border border-zinc-200 dark:border-zinc-700 divide-x divide-zinc-200 dark:divide-zinc-700 overflow-hidden text-sm">
        <button type="button" wire:click="$set('viewMode', '{{ \App\Enums\ViewModeEnum::Grid->value }}')" class="px-3 py-1.5 flex items-center gap-1.5 transition-colors rounded-l-lg {{ $viewMode === \App\Enums\ViewModeEnum::Grid->value ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : 'bg-white dark:bg-zinc-800 text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
          <flux:icon.layout-grid class="size-4" />{{ __('Grid') }}
        </button>
        <button type="button" wire:click="$set('viewMode', '{{ \App\Enums\ViewModeEnum::List->value }}')" class="px-3 py-1.5 flex items-center gap-1.5 transition-colors rounded-r-lg {{ $viewMode === \App\Enums\ViewModeEnum::List->value ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : 'bg-white dark:bg-zinc-800 text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
          <flux:icon.list class="size-4" />{{ __('List') }}
        </button>
      </div>
    </div>
  </div>

  {{-- Mobile: Shuffle left, Filter+View right --}}
  <div class="flex items-center gap-ui sm:hidden">
    <flux:button wire:click="shuffle" icon="shuffle" size="sm" class="flex-1">
      {{ __('Shuffle') }}
    </flux:button>

    <div class="flex items-center gap-ui shrink-0">
      <flux:modal.trigger name="recipe-filters">
        <flux:button size="sm">
          <flux:icon.sliders-horizontal class="size-5" />
          @if ($this->activeFilterCount > 0)
            <flux:badge size="sm" color="lime">{{ $this->activeFilterCount }}</flux:badge>
          @endif
        </flux:button>
      </flux:modal.trigger>

      <div class="inline-flex shrink-0 rounded-lg border border-zinc-200 dark:border-zinc-700 divide-x divide-zinc-200 dark:divide-zinc-700 overflow-hidden">
        <button type="button" wire:click="$set('viewMode', '{{ \App\Enums\ViewModeEnum::Grid->value }}')" class="p-1.5 flex items-center transition-colors rounded-l-lg {{ $viewMode === \App\Enums\ViewModeEnum::Grid->value ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : 'bg-white dark:bg-zinc-800 text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
          <flux:icon.layout-grid class="size-4" />
        </button>
        <button type="button" wire:click="$set('viewMode', '{{ \App\Enums\ViewModeEnum::List->value }}')" class="p-1.5 flex items-center transition-colors rounded-r-lg {{ $viewMode === \App\Enums\ViewModeEnum::List->value ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : 'bg-white dark:bg-zinc-800 text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
          <flux:icon.list class="size-4" />
        </button>
      </div>
    </div>
  </div>

  @include('web::partials.recipes.filter-modal')

  @if ($this->randomRecipes->isEmpty())
    <div class="flex flex-col items-center justify-center py-16 text-center">
      <flux:icon.search-x class="size-12 text-zinc-400" />
      <flux:heading size="lg" class="mt-4">{{ __('No recipes found') }}</flux:heading>
      <flux:text class="mt-2 text-zinc-500">{{ __('No recipes match your current filter settings.') }}</flux:text>

      @if ($this->activeFilterCount > 0)
        <flux:button wire:click="clearFilters" variant="primary" class="mt-section">
          {{ __('Clear All Filters') }}
        </flux:button>
      @endif
    </div>
  @else
    @if ($viewMode === \App\Enums\ViewModeEnum::Grid->value)
      <div class="grid grid-cols-1 gap-section sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach ($this->randomRecipes as $recipe)
          <x-recipes.recipe-card wire:key="recipe-{{ $recipe->id }}" :recipe="$recipe" :view-mode="\App\Enums\ViewModeEnum::Grid" :tag-ids="$tagIds" />
        @endforeach
      </div>
    @else
      <div class="flex flex-col gap-4">
        @foreach ($this->randomRecipes as $recipe)
          <x-recipes.recipe-card wire:key="recipe-{{ $recipe->id }}" :recipe="$recipe" :view-mode="\App\Enums\ViewModeEnum::List" :tag-ids="$tagIds" />
        @endforeach
      </div>
    @endif
  @endif

  @if ($this->activeFilterCount === 0)
    <div class="flex justify-center">
      <flux:modal.trigger name="share-url-direct">
        <flux:button icon="share-2" variant="ghost">
          {{ __('Share') }}
        </flux:button>
      </flux:modal.trigger>
    </div>

    <flux:modal name="share-url-direct" class="md:w-96">
      <div class="space-y-ui">
        <flux:heading size="lg">{{ __('Share Link') }}</flux:heading>
        <flux:input :value="$this->getDirectShareUrl()" readonly copyable />
      </div>
    </flux:modal>
  @else
    <form wire:submit="prepareShareUrl" class="flex justify-center">
      <flux:modal.trigger name="share-url-filter">
        <flux:button type="submit" icon="share-2" variant="ghost">
          {{ __('Share') }}
        </flux:button>
      </flux:modal.trigger>
    </form>

    <flux:modal name="share-url-filter" class="md:w-96">
      <div class="space-y-ui">
        <flux:heading size="lg">{{ __('Share Link') }}</flux:heading>
        <div wire:loading wire:target="prepareShareUrl" class="flex justify-center py-4">
          <flux:icon.loading class="size-6" />
        </div>
        <div wire:loading.remove wire:target="prepareShareUrl">
          <flux:input wire:model="shareUrl" readonly copyable />
        </div>
      </div>
    </flux:modal>
  @endif
</main>
