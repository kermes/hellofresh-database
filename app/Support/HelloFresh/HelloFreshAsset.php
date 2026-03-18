<?php

namespace App\Support\HelloFresh;

use Illuminate\Support\Facades\Storage;

class HelloFreshAsset
{
    /**
     * Generate an asset URL for a HelloFresh cloud media.
     *
     * Returns a local storage URL when the image has been downloaded,
     * otherwise falls back to the HelloFresh CDN.
     */
    public static function url(?string $imagePath, string $transformation): ?string
    {
        if ($imagePath === null || $imagePath === '') {
            return null;
        }

        $localPath = 'hellofresh' . $imagePath;

        if (Storage::disk('public')->exists($localPath)) {
            return Storage::disk('public')->url($localPath);
        }

        $baseUrl = config('hellofresh.cdn.base_url');
        $bucket = config('hellofresh.cdn.bucket');

        return $baseUrl . '/' . $transformation . '/' . $bucket . $imagePath;
    }

    /**
     * Generate a recipe card image URL.
     */
    public static function recipeCard(?string $imagePath): ?string
    {
        return self::url($imagePath, config('hellofresh.assets.recipe.card'));
    }

    /**
     * Generate a recipe header image URL.
     */
    public static function recipeHeader(?string $imagePath): ?string
    {
        return self::url($imagePath, config('hellofresh.assets.recipe.header'));
    }

    /**
     * Generate an ingredient thumbnail URL.
     */
    public static function ingredientThumbnail(?string $imagePath): ?string
    {
        return self::url($imagePath, config('hellofresh.assets.ingredient.thumbnail'));
    }

    /**
     * Generate a step image URL.
     */
    public static function stepImage(?string $imagePath): ?string
    {
        return self::url($imagePath, config('hellofresh.assets.step.image'));
    }
}
