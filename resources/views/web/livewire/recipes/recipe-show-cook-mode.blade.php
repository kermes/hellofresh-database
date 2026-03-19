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
            @for ($dotIndex = 0; $dotIndex < $cookingTotalSlides; $dotIndex++)
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
