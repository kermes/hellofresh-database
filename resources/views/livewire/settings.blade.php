<main class="mx-auto max-w-xl lg:max-w-3xl px-4 sm:px-6 lg:px-8">
  <flux:heading size="xl">{{ __('Settings') }}</flux:heading>

  <flux:separator variant="subtle" class="my-8" />

  {{-- Profile Information --}}
  <form wire:submit="updateProfile">
    <div class="flex flex-col lg:flex-row gap-4 lg:gap-6">
      <div class="lg:w-80">
        <flux:heading size="lg">{{ __('Profile Information') }}</flux:heading>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __("Update your account's profile information and email address.") }}</p>
      </div>

      <div class="flex-1 space-y-section">
        <flux:input
          wire:model="name"
          :label="__('Name')"
          :placeholder="__('Your name')"
          required
        />

        <flux:input
          wire:model="email"
          type="email"
          :label="__('Email')"
          placeholder="you@example.com"
          required
        />

        <x-country-select wire:model="country_code" />

        <div class="flex justify-end">
          <flux:button type="submit" variant="primary">
            {{ __('Save') }}
          </flux:button>
        </div>
      </div>
    </div>
  </form>

  <flux:separator variant="subtle" class="my-8" />

  {{-- Avatar --}}
  <form wire:submit="updateAvatar">
    <div class="flex flex-col lg:flex-row gap-4 lg:gap-6">
      <div class="lg:w-80">
        <flux:heading size="lg">{{ __('Avatar') }}</flux:heading>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Upload a profile picture. Image must be square and between 200x200 and 1000x1000 pixels.') }}</p>
      </div>

      <div class="flex-1 space-y-ui">
        <div class="flex items-center gap-section">
          <flux:avatar
            :src="$this->avatarUrl"
            :name="auth()->user()->name"
            size="xl"
            circle
          />
          @if($this->avatarUrl && !$avatar)
            <flux:button wire:click="removeAvatar" type="button" variant="danger" size="sm">
              {{ __('Remove') }}
            </flux:button>
          @endif
        </div>

        <div>
          <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">{{ __('Upload new avatar') }}</label>
          <div class="rounded-lg border-2 border-dashed border-zinc-300 dark:border-zinc-600 p-6 text-center">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Drop image here or click to browse') }}</p>
            <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">{{ __('JPG up to 2MB (200x200 - 1000x1000px, square)') }}</p>
            <input
              type="file"
              wire:model="avatar"
              accept="image/jpeg,image/png,image/gif,image/webp"
              class="mt-3 block w-full text-sm text-zinc-500 file:mr-3 file:rounded-md file:border-0 file:bg-zinc-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-700 dark:file:text-zinc-300 dark:hover:file:bg-zinc-600"
            />
          </div>
        </div>

        @if($avatar)
          <x-ui.file-item
            :heading="$avatar->getClientOriginalName()"
            :image="$avatar->isPreviewable() ? $avatar->temporaryUrl() : null"
            :size="$avatar->getSize()"
          >
            <x-slot name="actions">
              <button
                type="button"
                wire:click="cancelAvatarUpload"
                :aria-label="__('Remove file')"
                class="rounded p-1 text-zinc-400 hover:text-red-500 hover:bg-zinc-100 dark:hover:bg-zinc-700"
              >
                <flux:icon.x class="size-4" />
              </button>
            </x-slot>
          </x-ui.file-item>

          <div class="flex justify-end gap-ui">
            <flux:button type="button" wire:click="cancelAvatarUpload">
              {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary">
              {{ __('Save Avatar') }}
            </flux:button>
          </div>
        @endif
      </div>
    </div>
  </form>

  <flux:separator variant="subtle" class="my-8" />

  {{-- Update Password --}}
  <form wire:submit="updatePassword">
    <div class="flex flex-col lg:flex-row gap-4 lg:gap-6">
      <div class="lg:w-80">
        <flux:heading size="lg">{{ __('Update Password') }}</flux:heading>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
      </div>

      <div class="flex-1 space-y-section">
        <flux:input
          wire:model="current_password"
          type="password"
          :label="__('Current Password')"
          :placeholder="__('Your current password')"
          required
        />

        <flux:input
          wire:model="password"
          type="password"
          :label="__('New Password')"
          :placeholder="__('Your new password')"
          required
        />

        <flux:input
          wire:model="password_confirmation"
          type="password"
          :label="__('Confirm Password')"
          :placeholder="__('Confirm your new password')"
          required
        />

        <div class="flex justify-end">
          <flux:button type="submit" variant="primary">
            {{ __('Update Password') }}
          </flux:button>
        </div>
      </div>
    </div>
  </form>

  <flux:separator variant="subtle" class="my-8" />

  {{-- Support --}}
  <div class="flex flex-col lg:flex-row gap-4 lg:gap-6 pb-10">
    <div class="lg:w-80">
      <flux:heading size="lg">{{ __('Support') }}</flux:heading>
      <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Information for support requests.') }}</p>
    </div>

    <div class="flex-1">
      <flux:field>
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Your User ID for support requests:') }}</label>
        <flux:input icon="hash" :value="auth()->id()" readonly copyable />
      </flux:field>
    </div>
  </div>
</main>
