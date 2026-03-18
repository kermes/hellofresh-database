<?php

namespace App\Console\Commands;

use App\Contracts\LauncherCommandInterface;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'images:download')]
class DownloadImagesCommand extends Command implements LauncherCommandInterface
{
    /**
     * The transformation to apply when downloading recipe images.
     */
    protected string $recipeTransformation = 'w_1200,q_auto,f_jpg,fl_lossy';

    /**
     * The transformation to apply when downloading ingredient images.
     */
    protected string $ingredientTransformation = 'w_200,q_auto,f_png';

    /**
     * The transformation to apply when downloading step images.
     */
    protected string $stepTransformation = 'w_600,q_auto,f_jpg,fl_lossy';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:download
                            {--type=all : The type of images to download (all, recipes, ingredients, steps)}
                            {--force : Re-download images that already exist locally}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download HelloFresh recipe and ingredient images to local storage';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $type = $this->option('type');
        $force = (bool) $this->option('force');

        $this->ensureStorageLinked();

        match ($type) {
            'recipes' => $this->downloadRecipeImages($force),
            'ingredients' => $this->downloadIngredientImages($force),
            'steps' => $this->downloadStepImages($force),
            default => $this->downloadAll($force),
        };

        $this->components->info('Image download complete.');
    }

    /**
     * Download all image types.
     */
    protected function downloadAll(bool $force): void
    {
        $this->downloadRecipeImages($force);
        $this->downloadIngredientImages($force);
        $this->downloadStepImages($force);
    }

    /**
     * Download all recipe images.
     */
    protected function downloadRecipeImages(bool $force): void
    {
        $this->components->info('Downloading recipe images...');

        $paths = Recipe::query()
            ->whereNotNull('image_path')
            ->pluck('image_path')
            ->unique()
            ->values();

        $this->downloadImages($paths, $this->recipeTransformation, $force);
    }

    /**
     * Download all ingredient images.
     */
    protected function downloadIngredientImages(bool $force): void
    {
        $this->components->info('Downloading ingredient images...');

        $paths = Ingredient::query()
            ->whereNotNull('image_path')
            ->pluck('image_path')
            ->unique()
            ->values();

        $this->downloadImages($paths, $this->ingredientTransformation, $force);
    }

    /**
     * Download all step images from recipe JSON columns.
     */
    protected function downloadStepImages(bool $force): void
    {
        $this->components->info('Downloading step images...');

        $paths = $this->collectStepImagePaths();

        $this->downloadImages($paths, $this->stepTransformation, $force);
    }

    /**
     * Collect all unique image paths from recipe step JSON columns.
     *
     * @return Collection<int, string>
     */
    protected function collectStepImagePaths(): Collection
    {
        $paths = collect();

        Recipe::query()
            ->where(function ($query): void {
                $query->whereNotNull('steps_primary')
                    ->orWhereNotNull('steps_secondary');
            })
            ->select(['steps_primary', 'steps_secondary'])
            ->each(function (Recipe $recipe) use (&$paths): void {
                $paths = $paths->merge($this->extractStepPaths($recipe->steps_primary));
                $paths = $paths->merge($this->extractStepPaths($recipe->steps_secondary));
            });

        return $paths->unique()->values();
    }

    /**
     * Extract image paths from a steps array.
     *
     * @param  array<int, array<string, mixed>>|null  $steps
     * @return Collection<int, string>
     */
    protected function extractStepPaths(?array $steps): Collection
    {
        if ($steps === null || $steps === []) {
            return collect();
        }

        return collect($steps)
            ->flatMap(function (array $step): array {
                /** @var array<int, array<string, string>> $images */
                $images = $step['images'] ?? [];

                return array_filter(
                    array_column($images, 'path'),
                    static fn (mixed $path): bool => is_string($path) && $path !== ''
                );
            });
    }

    /**
     * Download a collection of images to local storage.
     *
     * @param  Collection<int, string>  $paths
     */
    protected function downloadImages(Collection $paths, string $transformation, bool $force): void
    {
        if ($paths->isEmpty()) {
            $this->components->warn('No images found.');

            return;
        }

        $downloaded = 0;
        $skipped = 0;
        $failed = 0;

        $this->withProgressBar($paths, function (string $imagePath) use ($transformation, $force, &$downloaded, &$skipped, &$failed): void {
            $localPath = 'hellofresh' . $imagePath;

            if (! $force && Storage::disk('public')->exists($localPath)) {
                $skipped++;

                return;
            }

            $url = $this->buildDownloadUrl($imagePath, $transformation);

            try {
                $response = Http::timeout(30)->get($url);

                if (! $response->successful()) {
                    $failed++;

                    return;
                }

                Storage::disk('public')->put($localPath, $response->body());
                $downloaded++;
            } catch (ConnectionException) {
                $failed++;
            }
        });

        $this->newLine();
        $this->components->twoColumnDetail('Downloaded', (string) $downloaded);
        $this->components->twoColumnDetail('Skipped (already exist)', (string) $skipped);
        $this->components->twoColumnDetail('Failed', (string) $failed);
    }

    /**
     * Build a CDN download URL for the given image path and transformation.
     */
    protected function buildDownloadUrl(string $imagePath, string $transformation): string
    {
        $baseUrl = config('hellofresh.cdn.base_url');
        $bucket = config('hellofresh.cdn.bucket');

        return $baseUrl . '/' . $transformation . '/' . $bucket . $imagePath;
    }

    /**
     * Ensure the public storage symlink exists.
     */
    protected function ensureStorageLinked(): void
    {
        $publicPath = public_path('storage');

        if (! file_exists($publicPath)) {
            $this->callSilent('storage:link');
        }
    }
}
