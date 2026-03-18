<flux:modal name="auth-modal" class="max-w-md space-y-section">
  @if ($mode === 'login')
    <div wire:key="login-form" class="flex flex-col gap-section">
      <div>
        <flux:heading size="lg">{{ __('Login') }}</flux:heading>
        <flux:text class="mt-ui">{{ __('Sign in to access your favorites and saved lists.') }}</flux:text>
      </div>

      <form wire:submit="login" class="space-y-section">
        <flux:field>
          <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Email') }}</label>
          <flux:input wire:model="email" name="login_email" type="email" placeholder="{{ __('your@email.com') }}" />
          @error('email') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </flux:field>

        <flux:field>
          <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Password') }}</label>
          <flux:input wire:model="password" name="login_password" type="password" />
          @error('password') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </flux:field>

        <flux:field>
          <flux:checkbox wire:model="remember" label="{{ __('Remember me') }}" />
        </flux:field>

        <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="login">{{ __('Login') }}</span>
          <span wire:loading wire:target="login" class="flex items-center gap-ui">
            <flux:icon.loading class="size-4" />
            {{ __('Login') }}
          </span>
        </flux:button>
      </form>

      <div class="flex flex-col items-center gap-ui">
        <flux:button wire:click="switchToForgotPassword" variant="ghost" size="sm">
          {{ __('Forgot password?') }}
        </flux:button>
      </div>
    </div>
  @elseif ($mode === 'forgot-password')
    <div wire:key="forgot-password-form" class="flex flex-col gap-section">
      <div>
        <flux:heading size="lg">{{ __('Reset Password') }}</flux:heading>
        <flux:text class="mt-ui">{{ __('Enter your email address and we will send you a link to reset your password.') }}</flux:text>
      </div>

      @if ($resetLinkSent)
        <flux:callout variant="success" icon="check-circle">
          {{ __('We have emailed your password reset link.') }}
        </flux:callout>
      @else
        <form wire:submit="sendResetLink" class="space-y-section">
          <flux:field>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Email') }}</label>
            <flux:input wire:model="email" name="reset_email" type="email" placeholder="{{ __('your@email.com') }}" />
            @error('email') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
          </flux:field>

          <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="sendResetLink">{{ __('Send Reset Link') }}</span>
            <span wire:loading wire:target="sendResetLink" class="flex items-center gap-ui">
              <flux:icon.loading class="size-4" />
              {{ __('Send Reset Link') }}
            </span>
          </flux:button>
        </form>
      @endif

      <flux:separator />

      <div class="text-center">
        <flux:button wire:click="switchToLogin" variant="ghost" size="sm">
          {{ __('Back to Login') }}
        </flux:button>
      </div>
    </div>
  @endif
</flux:modal>
