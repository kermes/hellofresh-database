<div>
<main class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-section">

  {{-- Page header --}}
  <div class="flex items-center justify-between mb-section">
    <flux:heading size="xl">{{ __('Manage Users') }}</flux:heading>
    <flux:button
      wire:click="$set('showCreateForm', true)"
      variant="primary"
      icon="plus"
    >
      {{ __('New User') }}
    </flux:button>
  </div>

  {{-- Create form --}}
  @if ($showCreateForm)
    <flux:card class="mb-section">
      <flux:heading size="lg" class="mb-4">{{ __('New User') }}</flux:heading>
      <form wire:submit="create" class="space-y-section">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-ui">
          <flux:field>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Name') }}</label>
            <flux:input wire:model="newName" name="newName" type="text" :placeholder="__('Name')" autofocus />
            @error('newName') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
          </flux:field>

          <flux:field>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Email') }}</label>
            <flux:input wire:model="newEmail" name="newEmail" type="email" :placeholder="__('email@example.com')" />
            @error('newEmail') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
          </flux:field>

          <flux:field>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Password') }}</label>
            <flux:input wire:model="newPassword" name="newPassword" type="password" />
            @error('newPassword') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
          </flux:field>

          <flux:field>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Confirm Password') }}</label>
            <flux:input wire:model="newPasswordConfirmation" name="newPassword_confirmation" type="password" />
          </flux:field>

          <div class="sm:col-span-2">
            <x-country-select wire:model="newCountryCode" />
          </div>
        </div>

        <flux:field>
          <flux:checkbox wire:model="newIsAdmin" id="new-is-admin" :label="__('Admin')" />
        </flux:field>

        <div class="flex items-center gap-ui">
          <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="create">{{ __('Create User') }}</span>
            <span wire:loading wire:target="create" class="flex items-center gap-ui">
              <flux:icon.loading class="size-4" />
              {{ __('Creating...') }}
            </span>
          </flux:button>
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
      :placeholder="__('Search by name or email...')"
      icon="search"
    />
  </div>

  {{-- List --}}
  <flux:card>
    @if ($this->users->isEmpty())
      <p class="text-sm text-zinc-500 dark:text-zinc-400 py-4 text-center">
        {{ $search !== '' ? __('No users match your search.') : __('No users yet.') }}
      </p>
    @else
      <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
        @foreach ($this->users as $user)
          <li wire:key="user-{{ $user->id }}" class="flex items-center gap-3 py-3">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                {{ $user->name }}
              </p>
              <p class="text-sm text-zinc-500 dark:text-zinc-400 truncate">
                {{ $user->email }}
              </p>
            </div>

            <div class="flex items-center gap-ui shrink-0">
              @if ($user->admin)
                <flux:badge size="sm" color="amber">{{ __('Admin') }}</flux:badge>
              @endif
              @if ($user->country_code)
                <flux:badge size="sm" color="zinc">{{ $user->country_code }}</flux:badge>
              @endif
              <span class="text-xs text-zinc-400 dark:text-zinc-500">
                {{ $user->created_at->toDateString() }}
              </span>

              @if ($confirmingDeleteId === $user->id)
                <div class="flex items-center gap-ui">
                  <span class="text-xs text-red-600 dark:text-red-400">{{ __('Sure?') }}</span>
                  <flux:button wire:click="delete" variant="danger" size="sm">{{ __('Delete') }}</flux:button>
                  <flux:button wire:click="cancelDelete" variant="ghost" size="sm">{{ __('Cancel') }}</flux:button>
                </div>
              @elseif ($user->id !== auth()->id())
                <flux:button wire:click="confirmDelete({{ $user->id }})" variant="ghost" square size="sm" aria-label="{{ __('Delete user') }}">
                  <flux:icon.trash class="size-4 text-red-500" />
                </flux:button>
              @endif
            </div>
          </li>
        @endforeach
      </ul>

      {{-- Pagination --}}
      <div class="mt-4">
        {{ $this->users->links() }}
      </div>
    @endif
  </flux:card>

</main>
</div>
