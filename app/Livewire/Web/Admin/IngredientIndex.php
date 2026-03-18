<?php

namespace App\Livewire\Web\Admin;

use App\Livewire\AbstractComponent;
use App\Livewire\Web\Concerns\WithLocalizedContextTrait;
use App\Models\Ingredient;
use Illuminate\Contracts\View\View as ViewInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('web::components.layouts.localized')]
class IngredientIndex extends AbstractComponent
{
    use WithFileUploads;
    use WithLocalizedContextTrait;
    use WithPagination;

    public string $search = '';

    #[Locked]
    public ?int $editingId = null;

    public string $editingName = '';

    #[Locked]
    public ?string $editingExistingImagePath = null;

    public ?TemporaryUploadedFile $editingImage = null;

    public bool $showCreateForm = false;

    public string $newName = '';

    public ?TemporaryUploadedFile $newImage = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Paginated list of ingredients for the current country.
     *
     * @return LengthAwarePaginator<Ingredient>
     */
    #[Computed]
    public function ingredients(): LengthAwarePaginator
    {
        $query = Ingredient::where('country_id', $this->countryId)
            ->orderBy('name->' . $this->locale);

        if ($this->search !== '') {
            $query->whereLike('name->' . $this->locale, '%' . $this->search . '%');
        }

        return $query->paginate(25);
    }

    public function startEditing(int $ingredientId): void
    {
        $ingredient = Ingredient::find($ingredientId);
        if ($ingredient === null) {
            return;
        }

        $this->editingId = $ingredientId;
        $this->editingName = $ingredient->getTranslation('name', $this->locale) ?? '';
        $this->editingExistingImagePath = $ingredient->image_path;
        $this->editingImage = null;
    }

    public function cancelEditing(): void
    {
        $this->editingId = null;
        $this->editingName = '';
        $this->editingExistingImagePath = null;
        $this->editingImage = null;
    }

    public function saveEditing(): void
    {
        $this->validate([
            'editingName' => ['required', 'string', 'max:255'],
            'editingImage' => ['nullable', 'image', 'max:2048'],
        ]);

        $ingredient = Ingredient::findOrFail($this->editingId);
        $ingredient->setTranslation('name', $this->locale, $this->editingName);
        $ingredient->name_slug = Ingredient::normalizeToSlug($this->editingName);

        if ($this->editingImage instanceof TemporaryUploadedFile) {
            $filename = $this->editingImage->getClientOriginalName();
            $path = $this->editingImage->storeAs('hellofresh/ingredients', $filename, 'public');
            $ingredient->image_path = '/ingredients/' . basename((string) $path);
        }

        $ingredient->save();

        $this->editingId = null;
        $this->editingName = '';
        $this->editingExistingImagePath = null;
        $this->editingImage = null;
        unset($this->ingredients);
    }

    public function create(): void
    {
        $this->validate([
            'newName' => ['required', 'string', 'max:255'],
            'newImage' => ['nullable', 'image', 'max:2048'],
        ]);

        $ingredient = new Ingredient();
        $ingredient->setTranslation('name', $this->locale, $this->newName);
        $ingredient->name_slug = Ingredient::normalizeToSlug($this->newName);
        $ingredient->country_id = $this->countryId;

        if ($this->newImage instanceof TemporaryUploadedFile) {
            $filename = $this->newImage->getClientOriginalName();
            $path = $this->newImage->storeAs('hellofresh/ingredients', $filename, 'public');
            $ingredient->image_path = '/ingredients/' . basename((string) $path);
        }

        $ingredient->save();

        $this->newName = '';
        $this->newImage = null;
        $this->showCreateForm = false;
    }

    public function render(): ViewInterface
    {
        return view('web::livewire.admin.ingredient-index')
            ->title(page_title(__('Manage Ingredients')));
    }
}
