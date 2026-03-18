<div wire:key="add-to-list-{{ $recipeId }}">
  <button
    type="button"
    wire:click="openModal"
    @class([
        'rounded-full p-2 transition-colors',
        'bg-white/80 text-zinc-700 hover:bg-white dark:bg-zinc-800/80 dark:text-zinc-300 dark:hover:bg-zinc-800' => !$this->isInAnyList,
        'bg-blue-500 text-white hover:bg-blue-600' => $this->isInAnyList,
    ])
    title="{{ $this->isInAnyList ? __('In list') : __('Add to list') }}"
  >
    <flux:icon.list-plus variant="mini" />
  </button>

  @auth
    <flux:modal name="add-to-list-{{ $recipeId }}" class="max-w-sm flex flex-col gap-section" @close="closeModal">
      <flux:heading size="lg">{{ __('Add to List') }}</flux:heading>

      @if($isModalOpen)
        <flux:input wire:model.live="search" :placeholder="__('Search or create list...')" clearable />

        <div class="max-h-60 overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700 divide-y divide-zinc-100 dark:divide-zinc-800">
          @forelse($this->lists as $list)
            <label wire:key="list-{{ $list->id }}" class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/50 cursor-pointer">
              <input type="checkbox" wire:model="selectedLists" value="{{ $list->id }}" class="rounded border-zinc-300 dark:border-zinc-600">
              <span>{{ $list->name }}</span>
              @if($list->user_id !== auth()->id())
                <span class="text-zinc-400 text-xs">({{ $list->user->name }})</span>
              @endif
            </label>
          @empty
            <p class="px-3 py-2 text-sm text-zinc-500">{{ __('No lists found.') }}</p>
          @endforelse
        </div>

        @if(strlen($search) >= 2)
          <button type="button" wire:click="createList" class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100">
            <flux:icon.plus class="size-4" />
            {{ __('Create New List') }}: "{{ $search }}"
          </button>
        @endif

        <div class="flex justify-end pt-section">
          <flux:button wire:click="saveLists" variant="primary">{{ __('Save') }}</flux:button>
        </div>
      @endif
    </flux:modal>
  @endauth
</div>
