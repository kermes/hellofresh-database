@php
$cookingSlideNames = [__('Cover'), __('Overview')];
foreach ($this->steps as $step) {
$cookingSlideNames[] = __('Step :num', ['num' => $step['index']]);
}
$cookingTotalSlides = count($cookingSlideNames);
@endphp
<div
  x-data="{
    open: false,
    currentStep: 0,
    slideNames: @js($cookingSlideNames),
    totalSlides: {{ $cookingTotalSlides }},
    touchStartX: null,
    get prevName() { return this.currentStep > 0 ? this.slideNames[this.currentStep - 1] : null; },
    get nextName() { return this.currentStep < this.totalSlides - 1 ? this.slideNames[this.currentStep + 1] : null; },
    prev() { if (this.currentStep > 0) this.currentStep--; },
    next() { if (this.currentStep < this.totalSlides - 1) this.currentStep++; },
    openMode() { this.currentStep = 0; this.open = true; document.body.classList.add('overflow-hidden'); },
    close() { this.open = false; document.body.classList.remove('overflow-hidden'); },
    swipeStart(e) { this.touchStartX = e.touches[0].clientX; },
    swipeEnd(e) {
      if (this.touchStartX === null) return;
      const diff = this.touchStartX - e.changedTouches[0].clientX;
      if (Math.abs(diff) > 50) { diff > 0 ? this.next() : this.prev(); }
      this.touchStartX = null;
    },
  }"
  @keydown.escape.window="if (open) close()"
  @keydown.right.window="if (open) next()"
  @keydown.left.window="if (open) prev()">

  {{-- ============================================================ --}}
  {{-- COOKING MODE OVERLAY (teleported to <body> to escape DOM)   --}}
  {{-- ============================================================ --}}
  <template x-teleport="body">
    <div
      x-show="open"
      x-cloak
      @touchstart="swipeStart($event)"
      @touchend="swipeEnd($event)"
      class="fixed inset-0 z-[9999] flex flex-col bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white">
      {{-- Header --}}
      <div class="border-b border-zinc-200 dark:border-zinc-800 shrink-0" style="display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; padding: 0.75rem 1.5rem; gap: 1rem">
        <div class="flex items-center gap-3 min-w-0">
          <flux:icon.chef-hat class="size-5 text-lime-500 shrink-0" />
          <span class="font-semibold truncate text-sm">{{ $recipe->name }}</span>
        </div>
        <div class="flex items-center gap-3" style="color: #71717a; font-size: 0.875rem">
          <span x-text="slideNames[currentStep]"></span>
          <span>·</span>
          <span x-text="(currentStep + 1) + ' / ' + totalSlides"></span>
        </div>
        <div class="flex justify-end">
          <button
            type="button"
            x-on:click="close()"
            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-800"
            style="opacity: 0.6">
            <flux:icon.x class="size-4" />
            <span>{{ __('Close') }}</span>
          </button>
        </div>
      </div>

      {{-- Slide content --}}
      <div class="flex-1 overflow-hidden relative">

        {{-- Slide 0: Cover — title + image + ingredient thumbnails --}}
        <div
          x-show="currentStep === 0"
          class="h-full flex">
          {{-- Left: title block + image below --}}
          <div class="flex-1 flex flex-col min-w-0">
            {{-- Title block --}}
            <div class="shrink-0 p-4">
              @if ($recipe->label && $recipe->label->display_label)
              <span
                class="inline-block rounded px-2 py-1 text-xs font-semibold mb-4"
                style="background-color: {{ $recipe->label->background_color }}; color: {{ $recipe->label->foreground_color }}">{{ $recipe->label->name }}</span>
              @endif
              <h1 class="font-extrabold leading-tight" style="font-size: 3.5rem; line-height: 1.05">{{ $recipe->name }}</h1>
              @if ($recipe->headline)
              <p class="mt-3 text-2xl text-zinc-500 dark:text-zinc-400">{{ $recipe->headline }}</p>
              @endif
              <div class="flex items-center gap-6 mt-5 text-zinc-500 dark:text-zinc-400 text-xl">
                @if ($recipe->display_time)
                <div class="flex items-center gap-2">
                  <flux:icon.clock class="size-6" />
                  <span>{{ $recipe->display_time }} {{ __('min') }}</span>
                </div>
                @endif
                @if ($recipe->difficulty)
                <div class="flex items-center gap-2">
                  <flux:icon.chart-bar-big class="size-6" />
                  <span>{{ $recipe->difficulty }}/3</span>
                </div>
                @endif
                @if ($recipe->cuisines->isNotEmpty())
                <div class="flex items-center gap-2">
                  <flux:icon.globe class="size-6" />
                  <span>{{ $recipe->cuisines->pluck('name')->join(', ') }}</span>
                </div>
                @endif
              </div>
            </div>
            {{-- Image --}}
            <div class="flex-1 relative min-w-0">
              @if ($recipe->header_image_url)
              <img
                src="{{ $recipe->header_image_url }}"
                alt="{{ $recipe->name }}"
                class="absolute inset-0 w-full h-full object-cover">
              @else
              <div class="absolute inset-0 bg-zinc-200 dark:bg-zinc-800"></div>
              @endif
            </div>
          </div>

          {{-- Right: ingredient grid --}}
          <div class="w-80 xl:w-96 bg-zinc-100 dark:bg-zinc-800 overflow-y-auto shrink-0 p-6 flex flex-col">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-4">
              {{ __('Ingredients') }}
            </h3>
            <div class="grid grid-cols-2 gap-3 flex-1">
              @foreach ($this->ingredientsForYield as $item)
              @if ($item['ingredient'])
              <div class="flex flex-col items-center gap-2 text-center">
                @if ($item['ingredient']->image_path)
                <img
                  src="{{ \App\Support\HelloFresh\HelloFreshAsset::ingredientThumbnail($item['ingredient']->image_path) }}"
                  alt="{{ $item['ingredient']->name }}"
                  class="size-24 rounded-full object-cover ring-2 ring-zinc-300 dark:ring-zinc-600">
                @else
                <div class="size-24 rounded-full bg-zinc-200 dark:bg-zinc-700 ring-2 ring-zinc-300 dark:ring-zinc-600"></div>
                @endif
                <span class="text-xs text-zinc-600 dark:text-zinc-300 leading-tight">{{ $item['ingredient']->name }}</span>
              </div>
              @endif
              @endforeach
            </div>
          </div>
        </div>

        {{-- Slide 1: Overview / Summary --}}
        <div
          x-show="currentStep === 1"
          class="h-full flex">
          {{-- Left: ingredients --}}
          <div class="w-72 xl:w-80 bg-zinc-100 dark:bg-zinc-800 overflow-y-auto shrink-0 p-6 flex flex-col gap-6">
            <div>
              <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-3">
                {{ __('Ingredients') }}
              </h3>
              @if (count($this->availableYields) > 1)
              <div class="flex gap-1 mb-4">
                @foreach ($this->availableYields as $yield)
                <button
                  type="button"
                  x-on:click="$wire.set('selectedYield', {{ $yield }})"
                  class="rounded-lg px-3 py-1 text-sm font-semibold transition-colors {{ $selectedYield === $yield ? 'bg-lime-500 text-white' : 'bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-300 dark:hover:bg-zinc-600' }}">{{ $yield }}</button>
                @endforeach
              </div>
              @else
              <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-4">{{ __(':count servings', ['count' => $selectedYield]) }}</p>
              @endif
              <div class="space-y-3">
                @foreach ($this->ingredientsForYield as $item)
                @if ($item['ingredient'])
                <div class="flex items-center gap-3">
                  @if ($item['ingredient']->image_path)
                  <img
                    src="{{ \App\Support\HelloFresh\HelloFreshAsset::ingredientThumbnail($item['ingredient']->image_path) }}"
                    alt="{{ $item['ingredient']->name }}"
                    class="size-8 rounded-full object-cover shrink-0">
                  @else
                  <div class="size-8 rounded-full bg-zinc-200 dark:bg-zinc-700 shrink-0"></div>
                  @endif
                  <span class="flex-1 text-sm text-zinc-800 dark:text-zinc-200">{{ $item['ingredient']->name }}</span>
                  <span class="text-sm text-zinc-500 dark:text-zinc-400 shrink-0">
                    @if ($item['amount']){{ $item['amount'] }} @endif{{ $item['unit'] }}
                  </span>
                </div>
                @endif
                @endforeach
              </div>
            </div>
          </div>

          {{-- Right: description + meta + nutrition + allergens + utensils --}}
          <div class="flex-1 overflow-y-auto min-w-0 p-4">
            <h2 class="font-extrabold mb-3" style="font-size: 3rem; line-height: 1.1">{{ $recipe->name }}</h2>
            @if ($recipe->headline)
            <p class="text-2xl text-zinc-500 dark:text-zinc-400 mb-8">{{ $recipe->headline }}</p>
            @endif

            @if ($recipe->description)
            <p class="text-lg text-zinc-600 dark:text-zinc-300 leading-relaxed" style="margin-top: 1rem; margin-bottom: 1rem">{{ $recipe->description }}</p>
            @endif

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-8">
              @if ($recipe->prep_time)
              <div class="bg-zinc-100 dark:bg-zinc-800 rounded-xl p-4">
                <div class="text-zinc-500 dark:text-zinc-400 text-xs uppercase tracking-wider mb-1">{{ __('Prep time') }}</div>
                <div class="text-2xl font-bold">{{ $recipe->prep_time }}<span class="text-sm font-normal text-zinc-500 dark:text-zinc-400 ml-1">{{ __('min') }}</span></div>
              </div>
              @endif
              @if ($recipe->total_time)
              <div class="bg-zinc-100 dark:bg-zinc-800 rounded-xl p-4">
                <div class="text-zinc-500 dark:text-zinc-400 text-xs uppercase tracking-wider mb-1">{{ __('Total time') }}</div>
                <div class="text-2xl font-bold">{{ $recipe->total_time }}<span class="text-sm font-normal text-zinc-500 dark:text-zinc-400 ml-1">{{ __('min') }}</span></div>
              </div>
              @endif
              @if ($recipe->difficulty)
              <div class="bg-zinc-100 dark:bg-zinc-800 rounded-xl p-4">
                <div class="text-zinc-500 dark:text-zinc-400 text-xs uppercase tracking-wider mb-1">{{ __('Difficulty') }}</div>
                <div class="text-2xl font-bold">{{ $recipe->difficulty }}<span class="text-sm font-normal text-zinc-500 dark:text-zinc-400">/3</span></div>
              </div>
              @endif
              <div class="bg-zinc-100 dark:bg-zinc-800 rounded-xl p-4">
                <div class="text-zinc-500 dark:text-zinc-400 text-xs uppercase tracking-wider mb-1">{{ __('Servings') }}</div>
                <div class="text-2xl font-bold">{{ $selectedYield }}</div>
              </div>
              @if ($recipe->cuisines->isNotEmpty())
              <div class="bg-zinc-100 dark:bg-zinc-800 rounded-xl p-4 col-span-2">
                <div class="text-zinc-500 dark:text-zinc-400 text-xs uppercase tracking-wider mb-1">{{ __('Cuisine') }}</div>
                <div class="text-lg font-semibold">{{ $recipe->cuisines->pluck('name')->join(', ') }}</div>
              </div>
              @endif
            </div>

            @if (!empty($this->nutrition) || $recipe->utensils->isNotEmpty() || $recipe->allergens->isNotEmpty())
            <div style="margin-top: 3rem; display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
              <div>
                @if (!empty($this->nutrition))
                <h3 class="text-base font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-3">{{ __('Nutrition') }} · {{ __('per serving') }}</h3>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                  @foreach ($this->nutrition as $nutrient)
                  <div class="flex justify-between items-baseline py-1.5">
                    <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $nutrient['name'] }}</span>
                    <span class="text-sm font-semibold tabular-nums">{{ $nutrient['amount'] }} {{ $nutrient['unit'] }}</span>
                  </div>
                  @endforeach
                </div>
                @endif
              </div>

              <div>
                @if ($recipe->utensils->isNotEmpty())
                <h3 class="text-base font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-3">{{ __('Utensils') }}</h3>
                <div class="flex flex-wrap gap-2">
                  @foreach ($recipe->utensils as $utensil)
                  <span class="rounded-full bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 px-3 py-1 text-sm text-zinc-700 dark:text-zinc-300">{{ $utensil->name }}</span>
                  @endforeach
                </div>
                @endif
              </div>

              <div>
                @if ($recipe->allergens->isNotEmpty())
                <h3 class="text-base font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-3">{{ __('Allergens') }}</h3>
                <div class="flex flex-wrap gap-2">
                  @foreach ($recipe->allergens as $allergen)
                  <span class="rounded-full bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-700/50 px-3 py-1 text-sm text-red-700 dark:text-red-300">{{ $allergen->name }}</span>
                  @endforeach
                </div>
                @endif
              </div>
            </div>
            @endif
          </div>
        </div>

        {{-- Slides 2+: Recipe steps --}}
        @foreach ($this->steps as $stepIndex => $step)
        <div
          x-show="currentStep === {{ $stepIndex + 2 }}"
          class="h-full flex">
          {{-- Left sidebar: ingredients --}}
          <div class="w-72 xl:w-80 bg-zinc-100 dark:bg-zinc-800 overflow-y-auto shrink-0 p-6 flex flex-col">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-3">
              {{ __('Ingredients') }}
            </h3>
            @if (count($this->availableYields) > 1)
            <div class="flex gap-1 mb-4">
              @foreach ($this->availableYields as $yield)
              <button
                type="button"
                x-on:click="$wire.set('selectedYield', {{ $yield }})"
                class="rounded-lg px-3 py-1 text-sm font-semibold transition-colors {{ $selectedYield === $yield ? 'bg-lime-500 text-white' : 'bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-300 dark:hover:bg-zinc-600' }}">{{ $yield }}</button>
              @endforeach
            </div>
            @else
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-4">{{ __(':count servings', ['count' => $selectedYield]) }}</p>
            @endif
            <div class="space-y-3">
              @foreach ($this->ingredientsForYield as $item)
              @if ($item['ingredient'])
              <div class="flex items-center gap-3">
                @if ($item['ingredient']->image_path)
                <img
                  src="{{ \App\Support\HelloFresh\HelloFreshAsset::ingredientThumbnail($item['ingredient']->image_path) }}"
                  alt="{{ $item['ingredient']->name }}"
                  class="size-8 rounded-full object-cover shrink-0">
                @else
                <div class="size-8 rounded-full bg-zinc-200 dark:bg-zinc-700 shrink-0"></div>
                @endif
                <span class="flex-1 text-sm text-zinc-700 dark:text-zinc-300 leading-tight">{{ $item['ingredient']->name }}</span>
                <span class="text-xs text-zinc-500 shrink-0">
                  @if ($item['amount']){{ $item['amount'] }} @endif{{ $item['unit'] }}
                </span>
              </div>
              @endif
              @endforeach
            </div>
          </div>

          {{-- Right: step content --}}
          <div class="flex-1 overflow-y-auto min-w-0 flex flex-col">
            {{-- Step image --}}
            @if (!empty($step['images']))
            <div class="relative shrink-0" style="height: 45%">
              <img
                src="{{ \App\Support\HelloFresh\HelloFreshAsset::stepImage($step['images'][0]['path']) }}"
                alt="{{ $step['images'][0]['caption'] ?? '' }}"
                class="absolute inset-0 w-full h-full object-cover">
              <div class="absolute bottom-4 left-4 flex items-center justify-center rounded-full bg-lime-500 text-white font-bold shadow-lg" style="width: 2.5rem; height: 2.5rem; font-size: 1.125rem">
                {{ $step['index'] }}
              </div>
            </div>
            @endif

            {{-- Instructions --}}
            <div class="flex-1 p-4">
              <div class="cooking-mode-instructions" style="font-size: 1.75rem; line-height: 1.5">
                {!! $step['instructions'] !!}
              </div>
            </div>
          </div>
        </div>
        @endforeach

      </div>{{-- /slide content --}}

      {{-- Footer navigation --}}
      <div class="border-t border-zinc-200 dark:border-zinc-800 px-6 py-4 shrink-0" style="display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; gap: 1rem">
        {{-- Prev button --}}
        <div class="flex justify-start">
          <button
            type="button"
            x-on:click="prev()"
            x-bind:disabled="currentStep === 0"
            class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors disabled:opacity-30 disabled:cursor-not-allowed text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800">
            <flux:icon.arrow-left class="size-4 shrink-0" />
            <span x-text="prevName ?? ''" class="hidden sm:block truncate max-w-40"></span>
          </button>
        </div>

        {{-- Step label + dots (always centered) --}}
        <div class="flex flex-col items-center gap-2">
          <span
            x-text="currentStep >= 2 ? '{{ __('Step') }} ' + (currentStep - 1) + ' {{ __('of') }} {{ count($this->steps) }}' : slideNames[currentStep]"
            class="text-sm font-semibold text-zinc-600 dark:text-zinc-300"></span>
          <div class="flex items-center gap-1.5">
            @if ($cookingTotalSlides <= 14)
              @for ($dotIndex=0; $dotIndex < $cookingTotalSlides; $dotIndex++)
              <button
              type="button"
              x-on:click="currentStep = {{ $dotIndex }}"
              x-bind:style="currentStep === {{ $dotIndex }}
                ? 'width: 1.5rem; background-color: #84cc16'
                : 'width: 0.5rem; background-color: {{ $dotIndex >= 2 ? '#bbf7d0' : '#d4d4d8' }}'"
              class="h-2 rounded-full transition-all duration-200 shrink-0"
              title="{{ $cookingSlideNames[$dotIndex] }}"></button>
              @endfor
              @else
              <span class="text-sm text-zinc-500 dark:text-zinc-400" x-text="(currentStep + 1) + ' / ' + totalSlides"></span>
              @endif
          </div>
        </div>

        {{-- Next button --}}
        <div class="flex justify-end">
          <button
            type="button"
            x-on:click="next()"
            x-bind:disabled="currentStep === totalSlides - 1"
            class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors disabled:opacity-30 disabled:cursor-not-allowed text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800">
            <span x-text="nextName ?? ''" class="hidden sm:block truncate max-w-40"></span>
            <flux:icon.arrow-left class="size-4 rotate-180 shrink-0" />
          </button>
        </div>
      </div>
    </div>{{-- /cooking mode overlay --}}
  </template>

  <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 print:hidden">
    {{-- Header with image --}}
    <div class="relative -mx-4 -mt-4 sm:-mx-6 sm:-mt-6 lg:-mx-8 lg:-mt-8">
      @if ($recipe->header_image_url)
      <img
        src="{{ $recipe->header_image_url }}"
        alt="{{ $recipe->name }}"
        class="w-full h-64 sm:h-80 lg:h-96 object-cover">
      <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
      @endif

      <div class="absolute bottom-0 left-0 right-0 p-4 sm:p-6 lg:p-8 text-white">
        <div class="flex items-center gap-2 mb-2">
          @if ($recipe->label && $recipe->label->display_label)
          <span
            class="rounded px-2 py-1 text-xs font-semibold"
            style="background-color: {{ $recipe->label->background_color }}; color: {{ $recipe->label->foreground_color }}">
            {{ $recipe->label->name }}
          </span>
          @endif
          @foreach ($recipe->tags->where('display_label', true)->take(3) as $tag)
          <flux:badge size="sm">{{ $tag->name }}</flux:badge>
          @endforeach
        </div>
        <flux:heading size="2xl" class="text-white!">{{ $recipe->name }}</flux:heading>
        @if ($recipe->headline)
        <flux:text class="mt-1 text-white/80">{{ $recipe->headline }}</flux:text>
        @endif
      </div>
    </div>

    {{-- Unpublished notice --}}
    @if (!$recipe->published)
    <flux:callout variant="warning" class="mt-4">
      <flux:callout.heading>{{ __('Recipe No Longer Available on HelloFresh') }}</flux:callout.heading>
      <flux:callout.text>
        {{ __('This recipe has been removed from HelloFresh\'s active menu. You can still view and use the recipe here, but it is no longer available on the HelloFresh website.') }}
      </flux:callout.text>
    </flux:callout>
    @endif

    {{-- Quick info bar --}}
    <div class="flex flex-wrap items-center gap-4 py-4 border-b border-zinc-200 dark:border-zinc-700">
      @php
      /** @var \App\Models\Recipe $recipe */
      @endphp
      @if ($recipe->display_time)
      <div class="flex items-center gap-2">
        <flux:icon.clock variant="mini" class="text-zinc-500" />
        <span>{{ $recipe->display_time }} {{ __('min') }}</span>
      </div>
      @endif
      @if ($recipe->difficulty)
      <div class="flex items-center gap-2">
        <flux:icon.chart-bar-big variant="mini" class="text-zinc-500" />
        <span>{{ __('Difficulty') }}: {{ $recipe->difficulty }}/3</span>
      </div>
      @endif
      @if ($recipe->cuisines->isNotEmpty())
      <div class="flex items-center gap-2">
        <flux:icon.globe variant="mini" class="text-zinc-500" />
        <span>{{ $recipe->cuisines->pluck('name')->join(', ') }}</span>
      </div>
      @endif

      <div class="flex items-center gap-2 ml-auto">
        @auth
        @if (auth()->user()->admin)
        <a
          href="{{ localized_route('localized.recipes.edit', ['recipe' => $recipe->id]) }}"
          wire:navigate
          class="rounded-full p-2 transition-colors bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
          title="{{ __('Edit Recipe') }}">
          <flux:icon.pencil variant="mini" />
        </a>
        @endif
        @endauth
        @if ($recipe->hellofresh_url)
        <a
          href="{{ $recipe->hellofresh_url }}"
          target="_blank"
          rel="noopener noreferrer"
          class="rounded-full p-2 transition-colors bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
          title="{{ __('View on HelloFresh') }}">
          <flux:icon.external-link variant="mini" />
        </a>
        @endif
        @if ($recipe->pdf_url)
        <a
          href="{{ $recipe->pdf_url }}"
          target="_blank"
          rel="noopener noreferrer"
          class="rounded-full p-2 transition-colors bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
          title="{{ __('View PDF') }}">
          <flux:icon.file-text variant="mini" />
        </a>
        @endif
        <livewire:web.recipes.add-to-list-button :recipe-id="$recipe->id" />
        <button
          type="button"
          x-data
          x-on:click.prevent.stop="$store.shoppingList?.toggle({{ $recipe->id }})"
          class="rounded-full p-2 transition-colors bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
          x-bind:class="$store.shoppingList?.has({{ $recipe->id }}) && 'bg-green-500! text-white! hover:bg-green-600!'"
          x-bind:title="$store.shoppingList?.has({{ $recipe->id }}) ? '{{ __('Remove from shopping list') }}' : '{{ __('Add to shopping list') }}'">
          <flux:icon.shopping-basket variant="mini" />
        </button>
        <button
          type="button"
          onclick="window.print()"
          class="rounded-full p-2 transition-colors bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
          title="{{ __('Print recipe') }}">
          <flux:icon.printer variant="mini" />
        </button>
        @if ($this->steps !== [])
        <button
          type="button"
          x-on:click="openMode()"
          class="rounded-full p-2 transition-colors bg-lime-500 text-white hover:bg-lime-600"
          title="{{ __('Cooking Mode') }}">
          <flux:icon.chef-hat variant="mini" />
        </button>
        @endif
      </div>
    </div>

    {{-- Description --}}
    @if ($recipe->description)
    <div class="pt-ui">
      <flux:text>{{ $recipe->description }}</flux:text>
    </div>
    @endif

    {{-- Info blocks: Allergens, Utensils, Tags --}}
    @if ($recipe->allergens->isNotEmpty() || $recipe->utensils->isNotEmpty() || $recipe->tags->isNotEmpty())
    <div class="space-y-section py-section border-b border-zinc-200 dark:border-zinc-700">
      @if ($recipe->allergens->isNotEmpty())
      <div>
        <flux:text class="text-sm text-zinc-500 mb-ui">{{ __('Allergens') }}</flux:text>
        <div class="flex flex-wrap gap-1">
          @foreach ($recipe->allergens as $allergen)
          <flux:badge size="sm" color="red">{{ $allergen->name }}</flux:badge>
          @endforeach
        </div>
      </div>
      @endif

      @if ($recipe->utensils->isNotEmpty())
      <div>
        <flux:text class="text-sm text-zinc-500 mb-ui">{{ __('Utensils') }}</flux:text>
        <div class="flex flex-wrap gap-1">
          @foreach ($recipe->utensils as $utensil)
          <flux:badge size="sm" color="zinc">{{ $utensil->name }}</flux:badge>
          @endforeach
        </div>
      </div>
      @endif

      @if ($recipe->tags->isNotEmpty())
      <div>
        <flux:text class="text-sm text-zinc-500 mb-ui">{{ __('Tags') }}</flux:text>
        <div class="flex flex-wrap gap-1">
          @foreach ($recipe->tags as $tag)
          <flux:badge size="sm" color="lime">{{ $tag->name }}</flux:badge>
          @endforeach
        </div>
      </div>
      @endif
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-section py-section">
      {{-- Left column: Ingredients --}}
      <div class="lg:col-span-1">
        <flux:card class="lg:sticky lg:top-20 overflow-hidden">
          <div class="lg:max-h-[calc(100vh-6rem)] lg:overflow-auto">
            <div class="flex items-center justify-between mb-4">
              <flux:heading size="lg">{{ __('Ingredients') }}</flux:heading>
              @if (count($this->availableYields) > 1)
              <flux:button.group>
                @foreach ($this->availableYields as $yield)
                <flux:button
                  size="sm"
                  :variant="$selectedYield === $yield ? 'primary' : 'outline'"
                  wire:click="$set('selectedYield', {{ $yield }})">
                  {{ $yield }}
                </flux:button>
                @endforeach
              </flux:button.group>
              @endif
            </div>

            <div class="space-y-3">
              @foreach ($this->ingredientsForYield as $item)
              @if ($item['ingredient'])
              <div class="flex items-center gap-3">
                @if ($item['ingredient']->image_path)
                <img
                  src="{{ \App\Support\HelloFresh\HelloFreshAsset::ingredientThumbnail($item['ingredient']->image_path) }}"
                  alt="{{ $item['ingredient']->name }}"
                  class="size-10 rounded object-cover">
                @else
                <div class="size-10 rounded bg-zinc-100 dark:bg-zinc-800"></div>
                @endif
                <div class="flex-1">
                  <flux:text class="font-medium">{{ $item['ingredient']->name }}</flux:text>
                </div>
                <flux:text class="text-zinc-500 shrink-0">
                  @if ($item['amount'])
                  {{ $item['amount'] }} {{ $item['unit'] }}
                  @else
                  {{ $item['unit'] }}
                  @endif
                </flux:text>
              </div>
              @endif
              @endforeach
            </div>
          </div>
        </flux:card>
      </div>

      {{-- Right column: Steps --}}
      <div class="lg:col-span-2 space-y-section">
        <flux:heading size="lg">{{ __('Preparation') }}</flux:heading>

        @foreach ($this->steps as $step)
        <div class="flex gap-4">
          <div class="shrink-0 size-8 rounded-full bg-lime-500 text-white flex items-center justify-center font-semibold">
            {{ $step['index'] }}
          </div>
          <div class="flex-1 space-y-3">
            @if (!empty($step['images']))
            <div class="flex gap-2 overflow-x-auto">
              @foreach ($step['images'] as $image)
              <img
                src="{{ \App\Support\HelloFresh\HelloFreshAsset::stepImage($image['path']) }}"
                alt="{{ $image['caption'] ?? '' }}"
                class="h-32 rounded object-cover shrink-0">
              @endforeach
            </div>
            @endif
            <flux:text>
              {!! $step['instructions'] !!}
            </flux:text>
          </div>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Nutrition --}}
    @if (!empty($this->nutrition))
    <div class="py-section border-t border-zinc-200 dark:border-zinc-700">
      <flux:heading size="lg" class="mb-4">{{ __('Nutrition') }} <span class="text-sm font-normal text-zinc-500">{{ __('per serving') }}</span></flux:heading>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        @foreach ($this->nutrition as $nutrient)
        <flux:card class="text-center">
          <flux:text class="text-2xl font-bold">{{ $nutrient['amount'] }}</flux:text>
          <flux:text class="text-sm text-zinc-500">{{ $nutrient['unit'] }}</flux:text>
          <flux:text class="text-sm font-medium mt-1">{{ $nutrient['name'] }}</flux:text>
        </flux:card>
        @endforeach
      </div>
    </div>
    @endif

    {{-- Variant Recipes --}}
    @if ($this->relatedVariants->isNotEmpty())
    <div x-data="{ open: false }" class="py-section border-t border-zinc-200 dark:border-zinc-700">
      <button type="button" x-on:click="open = !open" class="flex items-center gap-ui w-full text-left">
        <flux:heading size="lg">{{ __('Variants of this Recipe') }} ({{ $this->relatedVariants->count() }})</flux:heading>
        <flux:icon.chevron-down class="size-5 transition-transform" x-bind:class="open && 'rotate-180'" />
      </button>
      <div x-show="open" x-collapse class="mt-4">
        <x-web::recipes.recipe-grid :recipes="$this->relatedVariants" />
      </div>
    </div>
    @endif

    {{-- Similar Recipes --}}
    @if ($this->similarRecipes->isNotEmpty())
    <div class="py-section border-t border-zinc-200 dark:border-zinc-700">
      <flux:heading size="lg" class="mb-4">{{ __('Similar Recipes') }}</flux:heading>
      <x-recipes.recipe-grid :recipes="$this->similarRecipes" />
    </div>
    @endif

    {{-- Back link --}}
    <div class="py-section">
      <flux:button :href="localized_route('localized.recipes.index')" variant="ghost" icon="arrow-left">
        {{ __('Back to recipes') }}
      </flux:button>
    </div>
  </main>

  {{-- ============================================================ --}}
  {{-- PRINT-ONLY RECIPE CARD (landscape, 2 pages, PDF-style)       --}}
  {{-- ============================================================ --}}
  <div class="not-print:hidden">

    {{-- ── PAGE 1: title + big image (left) | ingredients sidebar (right) ── --}}
    <div class="rp-page rp-page-1">

      {{-- Top bar: label badge (right-aligned) --}}
      @if ($recipe->label && $recipe->label->display_label)
      <div class="rp-topbar">
        <span></span>
        <span
          class="rp-label-badge"
          style="background-color: {{ $recipe->label->background_color }}; color: {{ $recipe->label->foreground_color }}">{{ $recipe->label->name }}</span>
      </div>
      @endif

      {{-- Two-column body --}}
      <div class="rp-page-1-body">

        {{-- Left: title + hero image + meta strip --}}
        <div class="rp-page-1-main">
          <h1 class="rp-title">{{ $recipe->name }}</h1>
          @if ($recipe->headline)
          <p class="rp-headline">{{ $recipe->headline }}</p>
          @endif

          @if ($recipe->header_image_url)
          <div class="rp-hero">
            <img src="{{ $recipe->header_image_url }}" alt="{{ $recipe->name }}" class="rp-hero-img">
          </div>
          @endif

          @if ($recipe->description)
          <p class="rp-description">{{ $recipe->description }}</p>
          @endif

          <div class="rp-meta">
            @if ($recipe->prep_time)
            <div class="rp-meta-item">
              <span class="rp-meta-label">{{ __('Prep time') }}</span>
              <span class="rp-meta-value">{{ $recipe->prep_time }} {{ __('min') }}</span>
            </div>
            @endif
            @if ($recipe->total_time)
            <div class="rp-meta-item">
              <span class="rp-meta-label">{{ __('Total time') }}</span>
              <span class="rp-meta-value">{{ $recipe->total_time }} {{ __('min') }}</span>
            </div>
            @endif
            @if ($recipe->difficulty)
            <div class="rp-meta-item">
              <span class="rp-meta-label">{{ __('Difficulty') }}</span>
              <span class="rp-meta-value">{{ $recipe->difficulty }}/3</span>
            </div>
            @endif
            <div class="rp-meta-item">
              <span class="rp-meta-label">{{ __('Servings') }}</span>
              <span class="rp-meta-value">{{ $selectedYield }}</span>
            </div>
            @if ($recipe->cuisines->isNotEmpty())
            <div class="rp-meta-item">
              <span class="rp-meta-label">{{ __('Cuisine') }}</span>
              <span class="rp-meta-value">{{ $recipe->cuisines->pluck('name')->join(', ') }}</span>
            </div>
            @endif
          </div>
        </div>

        {{-- Right sidebar: utensils + ingredients grid (2 cols, no amounts) --}}
        <aside class="rp-ing-sidebar">
          @if ($recipe->utensils->isNotEmpty())
          <h3 class="rp-ing-sidebar-heading">{{ __('Utensils') }}</h3>
          <p class="rp-ing-sidebar-text">{{ $recipe->utensils->pluck('name')->join(', ') }}</p>
          @endif

          <h3 class="rp-ing-sidebar-heading {{ $recipe->utensils->isNotEmpty() ? 'rp-ing-sidebar-heading--spaced' : '' }}">
            {{ __('Ingredients') }}
          </h3>
          <p class="rp-ing-sidebar-sub">{{ __(':count servings', ['count' => $selectedYield]) }}</p>
          @php
          $ingCount = count(array_filter($this->ingredientsForYield, fn($i) => $i['ingredient'] !== null));
          $ingSizeClass = match(true) {
          $ingCount <= 8=> 'rp-ing-grid--lg',
            $ingCount <= 12=> 'rp-ing-grid--md',
              default => 'rp-ing-grid--sm',
              };
              @endphp
              <div class="rp-ing-grid {{ $ingSizeClass }}">
                @foreach ($this->ingredientsForYield as $item)
                @if ($item['ingredient'])
                <div class="rp-ing-card">
                  @if ($item['ingredient']->image_path)
                  <img
                    src="{{ \App\Support\HelloFresh\HelloFreshAsset::ingredientThumbnail($item['ingredient']->image_path) }}"
                    alt="{{ $item['ingredient']->name }}"
                    class="rp-ing-card-img">
                  @else
                  <div class="rp-ing-card-placeholder"></div>
                  @endif
                  <span class="rp-ing-card-name">{{ $item['ingredient']->name }}</span>
                </div>
                @endif
                @endforeach
              </div>
        </aside>

      </div>{{-- /rp-page-1-body --}}

    </div>{{-- /rp-page-1 --}}

    {{-- ── PAGE 2: sidebar (ingredients + nutrition + allergens + utensils) + steps grid ── --}}
    <div class="rp-page rp-page-2">

      {{-- Left sidebar --}}
      <aside class="rp-sidebar">

        <div class="rp-sidebar-section">
          <h3 class="rp-sidebar-heading">{{ __('Ingredients') }}</h3>
          <p class="rp-sidebar-sub">{{ __(':count servings', ['count' => $selectedYield]) }}</p>
          @foreach ($this->ingredientsForYield as $item)
          @if ($item['ingredient'])
          <div class="rp-sidebar-row">
            <span>{{ $item['ingredient']->name }}</span>
            <span class="rp-sidebar-row-value">
              @if ($item['amount']){{ $item['amount'] }} @endif{{ $item['unit'] }}
            </span>
          </div>
          @endif
          @endforeach
        </div>

        @if (!empty($this->nutrition))
        <div class="rp-sidebar-section">
          <h3 class="rp-sidebar-heading">{{ __('Nutrition') }}</h3>
          <p class="rp-sidebar-sub">{{ __('per serving') }}</p>
          @foreach ($this->nutrition as $nutrient)
          <div class="rp-sidebar-row">
            <span>{{ $nutrient['name'] }}</span>
            <span class="rp-sidebar-row-value">{{ $nutrient['amount'] }} {{ $nutrient['unit'] }}</span>
          </div>
          @endforeach
        </div>
        @endif

        @if ($recipe->allergens->isNotEmpty())
        <div class="rp-sidebar-section">
          <h3 class="rp-sidebar-heading">{{ __('Allergens') }}</h3>
          <p class="rp-sidebar-text">{{ $recipe->allergens->pluck('name')->join(', ') }}</p>
        </div>
        @endif

      </aside>

      {{-- Steps grid: each step is a card (number + image + text) --}}
      <div class="rp-steps">
        <h2 class="rp-steps-heading">{{ __('Preparation') }}</h2>
        @php
        $stepCols = match(true) {
        count($this->steps) <= 4=> 2,
          count($this->steps) <= 6=> 3,
            default => 4,
            };
            @endphp
            <div class="rp-step-grid rp-step-grid--{{ $stepCols }}col">
              @foreach ($this->steps as $step)
              <div class="rp-step">
                <div class="rp-step-num">{{ $step['index'] }}</div>
                @if (!empty($step['images']))
                <div class="rp-step-img-wrap">
                  <img
                    src="{{ \App\Support\HelloFresh\HelloFreshAsset::stepImage($step['images'][0]['path']) }}"
                    alt="{{ $step['images'][0]['caption'] ?? '' }}"
                    class="rp-step-img">
                </div>
                @else
                <div class="rp-step-img-empty"></div>
                @endif
                <div class="rp-step-instructions">{!! $step['instructions'] !!}</div>
              </div>
              @endforeach
            </div>
      </div>

    </div>{{-- /rp-page-2 --}}

  </div>{{-- /print wrapper --}}
</div>{{-- /livewire root --}}