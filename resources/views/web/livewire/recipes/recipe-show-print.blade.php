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
        $ingCount = count(array_filter($this->ingredientsForYield, fn($item) => $item['ingredient'] !== null));
        $ingSizeClass = match(true) {
            $ingCount <= 8  => 'rp-ing-grid--lg',
            $ingCount <= 12 => 'rp-ing-grid--md',
            default         => 'rp-ing-grid--sm',
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
          count($this->steps) <= 4 => 2,
          count($this->steps) <= 6 => 3,
          default                  => 4,
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
