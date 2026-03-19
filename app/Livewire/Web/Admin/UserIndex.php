<?php

namespace App\Livewire\Web\Admin;

use App\Livewire\AbstractComponent;
use App\Livewire\Actions\RegisterUserAction;
use App\Livewire\Web\Concerns\WithLocalizedContextTrait;
use App\Models\User;
use Illuminate\Contracts\View\View as ViewInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\WithPagination;

#[Layout('web::components.layouts.localized')]
class UserIndex extends AbstractComponent
{
    use WithLocalizedContextTrait;
    use WithPagination;

    public string $search = '';

    public bool $showCreateForm = false;

    public string $newName = '';

    public string $newEmail = '';

    public string $newPassword = '';

    public string $newPasswordConfirmation = '';

    public ?string $newCountryCode = null;

    public bool $newIsAdmin = false;

    #[Locked]
    public ?int $confirmingDeleteId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Paginated list of users.
     *
     * @return LengthAwarePaginator<User>
     */
    #[Computed]
    public function users(): LengthAwarePaginator
    {
        $query = User::query()->orderBy('name');

        if ($this->search !== '') {
            $query->where(function ($q): void {
                $q->whereLike('name', '%' . $this->search . '%')
                    ->orWhereLike('email', '%' . $this->search . '%');
            });
        }

        return $query->paginate(25);
    }

    public function create(): void
    {
        $validated = $this->validate([
            'newName' => RegisterUserAction::rules()['name'],
            'newEmail' => RegisterUserAction::rules()['email'],
            'newPassword' => ['required', 'confirmed', Password::defaults()],
            'newCountryCode' => RegisterUserAction::rules()['country_code'],
            'newIsAdmin' => ['boolean'],
        ], [], [
            'newName' => __('Name'),
            'newEmail' => __('Email'),
            'newPassword' => __('Password'),
            'newCountryCode' => __('Country'),
        ]);

        User::create([
            'name' => $validated['newName'],
            'email' => $validated['newEmail'],
            'password' => $validated['newPassword'],
            'country_code' => $validated['newCountryCode'],
            'admin' => $validated['newIsAdmin'],
        ]);

        $this->reset(['newName', 'newEmail', 'newPassword', 'newPasswordConfirmation', 'newCountryCode', 'newIsAdmin', 'showCreateForm']);
        unset($this->users);
    }

    public function confirmDelete(int $userId): void
    {
        $this->confirmingDeleteId = $userId;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    public function delete(): void
    {
        $user = User::findOrFail($this->confirmingDeleteId);

        abort_if($user->id === auth()->id(), 403, __('You cannot delete your own account.'));
        abort_if($user->id === 1, 403, __('You cannot delete the system user.'));

        $user->delete();

        $this->confirmingDeleteId = null;
        unset($this->users);
    }

    public function render(): ViewInterface
    {
        return view('web::livewire.admin.user-index')
            ->title(page_title(__('Manage Users')));
    }
}
