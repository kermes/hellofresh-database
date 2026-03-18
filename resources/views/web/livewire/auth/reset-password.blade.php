<main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 flex items-center justify-center min-h-[60vh]">
  <flux:card class="max-w-md w-full">
    <div class="flex flex-col gap-section">
      <div>
        <flux:heading size="lg">{{ __('Reset Password') }}</flux:heading>
        <flux:text class="mt-ui">{{ __('Enter your new password below.') }}</flux:text>
      </div>

      <form wire:submit="resetPassword" class="space-y-section">
        <flux:field>
          <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Email') }}</label>
          <flux:input wire:model="email" type="email" placeholder="{{ __('your@email.com') }}" />
          @error('email') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </flux:field>

        <flux:field>
          <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('New Password') }}</label>
          <flux:input wire:model="password" type="password" />
          @error('password') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </flux:field>

        <flux:field>
          <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Confirm Password') }}</label>
          <flux:input wire:model="password_confirmation" type="password" />
          @error('password_confirmation') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </flux:field>

        <flux:button type="submit" variant="primary" class="w-full">
          {{ __('Reset Password') }}
        </flux:button>
      </form>
    </div>
  </flux:card>
</main>
