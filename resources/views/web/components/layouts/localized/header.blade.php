<div x-data="{ mobileOpen: false }">

{{-- Mobile overlay --}}
<div
  x-show="mobileOpen"
  x-on:click="mobileOpen = false"
  class="fixed inset-0 z-40 bg-black/50 lg:hidden"
  x-transition:enter="transition ease-out duration-200"
  x-transition:enter-start="opacity-0"
  x-transition:enter-end="opacity-100"
  x-transition:leave="transition ease-in duration-150"
  x-transition:leave-start="opacity-100"
  x-transition:leave-end="opacity-0"
  x-cloak
></div>

{{-- Desktop + mobile header bar --}}
<header class="sticky top-0 z-40 bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 print:hidden">
  <div class="flex h-14 items-center px-4 gap-2">
    {{-- Brand --}}
    <flux:brand :href="localized_route('localized.recipes.index')" />

    {{-- Mobile hamburger --}}
    <button
      type="button"
      x-on:click="mobileOpen = true"
      class="lg:hidden rounded-md p-1.5 text-zinc-500 hover:bg-zinc-200 dark:hover:bg-zinc-800 hover:text-zinc-700 dark:hover:text-zinc-300"
    >
      <flux:icon.bars-2 class="size-5" />
    </button>

    {{-- Desktop navbar --}}
    <flux:navbar class="-mb-px max-lg:hidden">
      <flux:navbar.item icon="book-open" :href="localized_route('localized.recipes.index')" wire:navigate>
        {{ __('Recipes') }}
      </flux:navbar.item>

<flux:navbar.item icon="shuffle" :href="localized_route('localized.recipes.random')" wire:navigate>
        {{ __('Random') }}
      </flux:navbar.item>

      <flux:navbar.item icon="shopping-basket" :href="localized_route('localized.shopping-list.index')" wire:navigate>
        {{ __('Shopping List') }}
        <span
          x-data
          x-show="$store.shoppingList && $store.shoppingList.count > 0"
          x-text="$store.shoppingList ? $store.shoppingList.count : ''"
          class="ml-1 inline-flex items-center justify-center size-5 text-xs font-medium rounded-full bg-green-500 text-white"
        ></span>
      </flux:navbar.item>
    </flux:navbar>

    <div class="grow"></div>

    <flux:navbar class="gap-ui">
      {{-- Global Search --}}
      <flux:modal.trigger name="global-search" shortcut="cmd.k" class="max-lg:hidden">
        <flux:input as="button" :placeholder="__('Search...')" icon="search" kbd="⌘K" class="w-48" />
      </flux:modal.trigger>

      {{-- Display Settings --}}
      <flux:dropdown align="end" class="max-lg:hidden">
        <flux:button variant="subtle" square class="group" aria-label="{{ __('Display Settings') }}">
          <flux:icon.sliders-horizontal variant="mini" class="text-zinc-500 dark:text-white" />
        </flux:button>

        <flux:menu>
          <flux:menu.heading>{{ __('Appearance') }}</flux:menu.heading>
          <flux:menu.item x-data x-on:click="$flux.appearance = 'light'" icon="sun" x-bind:class="$flux.appearance === 'light' ? 'font-medium' : ''">{{ __('Light') }}</flux:menu.item>
          <flux:menu.item x-data x-on:click="$flux.appearance = 'dark'" icon="moon" x-bind:class="$flux.appearance === 'dark' ? 'font-medium' : ''">{{ __('Dark') }}</flux:menu.item>
          <flux:menu.item x-data x-on:click="$flux.appearance = 'system'" icon="computer-desktop" x-bind:class="$flux.appearance === 'system' ? 'font-medium' : ''">{{ __('System') }}</flux:menu.item>

          <flux:menu.separator />

          <flux:menu.heading>{{ __('Recipe Tags') }}</flux:menu.heading>
          <flux:menu.checkbox x-model="$store.settings.clickableTags" x-data>
            <flux:icon.mouse-pointer-click class="size-4 shrink-0" />{{ __('Clickable Tags') }}
          </flux:menu.checkbox>
        </flux:menu>
      </flux:dropdown>

      {{-- User Menu --}}
      @auth
        <flux:dropdown position="bottom" align="end">
          <flux:profile :avatar="auth()->user()->getFirstMediaUrl('avatar', 'sm')" :name="auth()->user()->name" />

          <flux:menu>
            <flux:menu.item :href="localized_route('localized.lists')" wire:navigate icon="list">{{ __('My Lists') }}</flux:menu.item>
            <flux:menu.item :href="localized_route('localized.saved-shopping-lists')" wire:navigate icon="bookmark">{{ __('Saved Shopping Lists') }}</flux:menu.item>
            <flux:menu.item :href="localized_route('localized.settings')" wire:navigate icon="cog-6-tooth">{{ __('Settings') }}</flux:menu.item>
            @if (auth()->user()->admin)
              <flux:menu.separator />
              <flux:menu.heading>{{ __('Admin') }}</flux:menu.heading>
              <flux:menu.item :href="localized_route('localized.recipes.create')" wire:navigate icon="plus">{{ __('Create Recipe') }}</flux:menu.item>
              <flux:menu.item :href="localized_route('localized.admin.ingredients')" wire:navigate icon="leaf">{{ __('Manage Ingredients') }}</flux:menu.item>
              <flux:menu.item :href="localized_route('localized.admin.users')" wire:navigate icon="users">{{ __('Manage Users') }}</flux:menu.item>
            @endif
            <flux:menu.separator />
            <flux:menu.item x-on:click="$dispatch('logout')" icon="arrow-right-start-on-rectangle" variant="danger">{{ __('Logout') }}</flux:menu.item>
          </flux:menu>
        </flux:dropdown>
      @else
        <flux:button variant="subtle" x-on:click="$dispatch('require-auth')">
          {{ __('Login') }}
        </flux:button>
      @endauth
    </flux:navbar>
  </div>
</header>

{{-- Mobile sidebar --}}
<aside
  x-show="mobileOpen"
  x-transition:enter="transition ease-out duration-200"
  x-transition:enter-start="-translate-x-full"
  x-transition:enter-end="translate-x-0"
  x-transition:leave="transition ease-in duration-150"
  x-transition:leave-start="translate-x-0"
  x-transition:leave-end="-translate-x-full"
  class="fixed inset-y-0 left-0 z-50 w-72 flex flex-col bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700 lg:hidden"
  x-cloak
>
  {{-- Sidebar header --}}
  <div class="flex items-center justify-between p-4 border-b border-zinc-200 dark:border-zinc-700">
    <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">{{ config('app.name') }}</span>
    <button
      type="button"
      x-on:click="mobileOpen = false"
      class="rounded-md p-1 text-zinc-500 hover:bg-zinc-200 dark:hover:bg-zinc-800"
    >
      <flux:icon.x class="size-5" />
    </button>
  </div>

  <nav class="flex-1 overflow-y-auto px-2 py-2 space-y-0.5">
    <button
      type="button"
      x-on:click="$flux.modal('global-search').show(); mobileOpen = false"
      class="flex w-full items-center gap-2 px-3 py-2 rounded-md text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-800 text-left"
    >
      <flux:icon.search class="size-4 shrink-0" />{{ __('Search') }}
    </button>

    <div class="my-ui border-t border-zinc-200 dark:border-zinc-700"></div>

    <a href="{{ localized_route('localized.recipes.index') }}" wire:navigate class="flex items-center gap-2 px-3 py-2 rounded-md text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-800">
      <flux:icon.book-open class="size-4 shrink-0" />{{ __('Recipes') }}
    </a>
<a href="{{ localized_route('localized.recipes.random') }}" wire:navigate class="flex items-center gap-2 px-3 py-2 rounded-md text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-800">
      <flux:icon.shuffle class="size-4 shrink-0" />{{ __('Random') }}
    </a>
    <a href="{{ localized_route('localized.shopping-list.index') }}" wire:navigate class="flex items-center gap-2 px-3 py-2 rounded-md text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-800">
      <flux:icon.shopping-basket class="size-4 shrink-0" />
      {{ __('Shopping List') }}
      <span
        x-data
        x-show="$store.shoppingList && $store.shoppingList.count > 0"
        x-text="$store.shoppingList ? $store.shoppingList.count : ''"
        class="ml-1 inline-flex items-center justify-center size-5 text-xs font-medium rounded-full bg-green-500 text-white"
      ></span>
    </a>

    @auth
      <div class="grow"></div>

      <a href="{{ localized_route('localized.lists') }}" wire:navigate class="flex items-center gap-2 px-3 py-2 rounded-md text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-800">
        <flux:icon.list class="size-4 shrink-0" />{{ __('My Lists') }}
      </a>
      <a href="{{ localized_route('localized.saved-shopping-lists') }}" wire:navigate class="flex items-center gap-2 px-3 py-2 rounded-md text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-800">
        <flux:icon.bookmark class="size-4 shrink-0" />{{ __('Saved Shopping Lists') }}
      </a>
      <a href="{{ localized_route('localized.settings') }}" wire:navigate class="flex items-center gap-2 px-3 py-2 rounded-md text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-800">
        <flux:icon.settings class="size-4 shrink-0" />{{ __('Settings') }}
      </a>

      @if (auth()->user()->admin)
        <div class="my-ui border-t border-zinc-200 dark:border-zinc-700"></div>
        <p class="px-3 py-1 text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">{{ __('Admin') }}</p>
        <a href="{{ localized_route('localized.recipes.create') }}" wire:navigate class="flex items-center gap-2 px-3 py-2 rounded-md text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-800">
          <flux:icon.plus class="size-4 shrink-0" />{{ __('Create Recipe') }}
        </a>
        <a href="{{ localized_route('localized.admin.ingredients') }}" wire:navigate class="flex items-center gap-2 px-3 py-2 rounded-md text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-800">
          <flux:icon.leaf class="size-4 shrink-0" />{{ __('Manage Ingredients') }}
        </a>
        <a href="{{ localized_route('localized.admin.users') }}" wire:navigate class="flex items-center gap-2 px-3 py-2 rounded-md text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-800">
          <flux:icon.users class="size-4 shrink-0" />{{ __('Manage Users') }}
        </a>
      @endif
    @endauth
  </nav>

  <div class="grow"></div>

  <div class="px-3 py-3 border-t border-zinc-200 dark:border-zinc-700 space-y-3">
    <div class="flex items-center justify-between">
      <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Clickable Tags') }}</span>
      <flux:switch x-model="$store.settings.clickableTags" size="sm" />
    </div>
    <flux:radio.group x-data variant="segmented" x-model="$flux.appearance" class="w-full" size="sm">
      <flux:radio value="light" icon="sun" />
      <flux:radio value="dark" icon="moon" />
      <flux:radio value="system" icon="computer-desktop" />
    </flux:radio.group>

    @guest
      <button
        type="button"
        x-on:click="$dispatch('require-auth'); mobileOpen = false"
        class="flex w-full items-center gap-2 px-3 py-2 rounded-md text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-800 text-left"
      >
        <flux:icon.log-in class="size-4 shrink-0" />{{ __('Login') }}
      </button>
    @else
      <button
        type="button"
        x-on:click="$dispatch('logout')"
        class="flex w-full items-center gap-2 px-3 py-2 rounded-md text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-800 text-left"
      >
        <flux:icon.log-out class="size-4 shrink-0" />{{ __('Logout') }}
      </button>
    @endguest
  </div>
</aside>

</div>
