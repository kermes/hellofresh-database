@props([
    'heading' => null,
    'image' => null,
    'size' => null,
])
@php
    $formattedSize = null;
    if ($size !== null) {
        $formattedSize = $size >= 1024 * 1024
            ? number_format($size / (1024 * 1024), 1) . ' MB'
            : number_format($size / 1024, 1) . ' KB';
    }
@endphp
<div {{ $attributes->class(['flex items-center gap-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-3']) }}>
    @if ($image)
        <img src="{{ $image }}" alt="{{ $heading }}" class="size-10 rounded object-cover shrink-0">
    @else
        <div class="size-10 rounded bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center shrink-0">
            <flux:icon.file class="size-5 text-zinc-400" />
        </div>
    @endif

    <div class="min-w-0 flex-1">
        @if ($heading)
            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">{{ $heading }}</p>
        @endif
        @if ($formattedSize)
            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $formattedSize }}</p>
        @endif
    </div>

    @if (isset($actions))
        <div class="shrink-0">
            {{ $actions }}
        </div>
    @endif
</div>
