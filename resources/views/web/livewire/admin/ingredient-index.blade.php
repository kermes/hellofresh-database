<div>
<main class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-section">

  {{-- Page header --}}
  <div class="flex items-center justify-between mb-section">
    <flux:heading size="xl">{{ __('Manage Ingredients') }}</flux:heading>
    <flux:button
      wire:click="$set('showCreateForm', true)"
      variant="primary"
      icon="plus"
    >
      {{ __('New Ingredient') }}
    </flux:button>
  </div>

  {{-- Create form --}}
  @if ($showCreateForm)
    <flux:card class="mb-section">
      <flux:heading size="lg" class="mb-4">{{ __('New Ingredient') }}</flux:heading>
      <form wire:submit="create" class="space-y-ui">
        <div class="flex items-end gap-ui">
          <div class="flex-1">
            <flux:input
              wire:model="newName"
              :label="__('Name')"
              :placeholder="__('Ingredient name...')"
              required
              autofocus
            />
            @error('newName')
              <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
          </div>

          <div class="shrink-0">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">{{ __('Image') }}</label>
            <div class="flex items-center gap-ui">
              @if ($newImage)
                <img src="{{ $newImage->temporaryUrl() }}" alt="" class="size-24 rounded object-cover shrink-0">
              @endif
              <label class="cursor-pointer inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 transition">
                <flux:icon.image class="size-4" />
                {{ $newImage ? __('Change') : __('Upload') }}
                <input type="file" wire:model="newImage" accept="image/*" class="sr-only">
              </label>
            </div>
            @error('newImage')
              <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
          </div>

          <flux:button type="submit" variant="primary">{{ __('Create') }}</flux:button>
          <flux:button type="button" variant="ghost" wire:click="$set('showCreateForm', false)">
            {{ __('Cancel') }}
          </flux:button>
        </div>
      </form>
    </flux:card>
  @endif

  {{-- Search --}}
  <div class="mb-4">
    <flux:input
      wire:model.live.debounce.300ms="search"
      :placeholder="__('Search ingredients...')"
      icon="search"
    />
  </div>

  {{-- List --}}
  <flux:card>
    @if ($this->ingredients->isEmpty())
      <p class="text-sm text-zinc-500 dark:text-zinc-400 py-4 text-center">
        {{ $search !== '' ? __('No ingredients match your search.') : __('No ingredients yet.') }}
      </p>
    @else
      <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
        @foreach ($this->ingredients as $ingredient)
          <li wire:key="ingredient-{{ $ingredient->id }}" class="flex items-center gap-3 py-2">
            {{-- Thumbnail (hidden while editing, the edit form shows its own preview) --}}
            @if ($editingId !== $ingredient->id)
              @if ($ingredient->image_path)
                <img
                  src="{{ \App\Support\HelloFresh\HelloFreshAsset::ingredientThumbnail($ingredient->image_path) }}"
                  alt=""
                  class="size-24 rounded object-cover shrink-0"
                >
              @else
                <div class="size-24 rounded bg-zinc-100 dark:bg-zinc-700 shrink-0"></div>
              @endif
            @endif

            {{-- Name / edit form --}}
            @if ($editingId === $ingredient->id)
              <form wire:submit="saveEditing" class="flex items-center gap-ui flex-1">
                <div class="flex items-center gap-ui shrink-0">
                  @if ($editingImage)
                    <img src="{{ $editingImage->temporaryUrl() }}" alt="" class="size-24 rounded object-cover">
                  @elseif ($editingExistingImagePath)
                    <img src="{{ \App\Support\HelloFresh\HelloFreshAsset::ingredientThumbnail($editingExistingImagePath) }}" alt="" class="size-24 rounded object-cover">
                  @else
                    <div class="size-24 rounded bg-zinc-100 dark:bg-zinc-700"></div>
                  @endif
                  <label class="cursor-pointer inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-md hover:bg-zinc-50 dark:hover:bg-zinc-700 transition">
                    <flux:icon.image class="size-3" />
                    {{ $editingImage || $editingExistingImagePath ? __('Change') : __('Image') }}
                    <input type="file" wire:model="editingImage" accept="image/*" class="sr-only">
                  </label>
                  @error('editingImage')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                  @enderror
                </div>
                <flux:input
                  wire:model="editingName"
                  size="sm"
                  required
                  autofocus
                  class="flex-1"
                />
                @error('editingName')
                  <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                <flux:button type="submit" variant="primary" size="sm">{{ __('Save') }}</flux:button>
                <flux:button type="button" variant="ghost" size="sm" wire:click="cancelEditing">
                  {{ __('Cancel') }}
                </flux:button>
              </form>
            @else
              <span class="flex-1 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                {{ $ingredient->name }}
              </span>

              <div class="flex items-center gap-1 shrink-0">
                @if ($ingredient->cached_recipes_count > 0)
                  <flux:badge size="sm" color="zinc">
                    {{ $ingredient->cached_recipes_count }} {{ __('recipes') }}
                  </flux:badge>
                @endif
                <flux:button
                  wire:click="startEditing({{ $ingredient->id }})"
                  variant="ghost"
                  square
                  size="sm"
                >
                  <flux:icon.pencil class="size-4" />
                </flux:button>
              </div>
            @endif
          </li>
        @endforeach
      </ul>

      {{-- Pagination --}}
      <div class="mt-4">
        {{ $this->ingredients->links() }}
      </div>
    @endif
  </flux:card>

</main>
</div>
