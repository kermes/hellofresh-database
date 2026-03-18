# HelloFresh Database

> **This is a personal fork of [Muetze42/hellofresh-database](https://github.com/Muetze42/hellofresh-database).** See [Changes from upstream](#changes-from-upstream) for what differs.

## Features from upstream

- Browse add and edit recipes from HelloFresh.es
- Filter by ingredients, allergens, tags, labels, difficulty, prep time, utensils
- Shopping list with ingredient aggregation
- Save and load shopping lists
- Create custom recipe lists
- Dark mode support

## Changes from upstream

- Focused on the Spanish HelloFresh market only — removed multi-country support from the main page due to [HelloFresh Spain's closure](https://www.hellofresh.es/pages/closure), making this a preservation archive for Spanish recipes
- Removed the Flux UI Pro dependency — replaced all Pro components with plain HTML + Tailwind CSS + Alpine.js equivalents, which caused a cascade of changes across Livewire components and Blade views
- Added an admin interface to create and edit recipes and ingredients directly in the app — this required making the entire app login-protected rather than publicly accessible as in the original
- Added a utensils filter
- Added a Cook Mode for step by step thermomix-like experience
- Added Docker setup (`docker/`)
- Added `DownloadImagesCommand` for downloading recipe images locally (for preservation in case they remove them)
- Added a "Print" version that resembles the PDF from hellofresh.
- Removed the weekly menu/plan browsing feature — HelloFresh Spain will no longer publish new weekly plans following their closure
- Removed the API Portal including auth, stats, token management, and docs sections
- Removed the Personal Access Token API system (models, migrations, middleware)

## Acknowledgements

Thanks to [Norman Huth (Muetze42)](https://github.com/Muetze42) for building and open-sourcing the original project that this fork is based on.
