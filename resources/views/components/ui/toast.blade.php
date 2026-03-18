<div
    x-data="{
        toasts: [],
        add(toast) {
            const id = Date.now();
            this.toasts.push({ id, ...toast });
            setTimeout(() => this.remove(id), toast.duration ?? 5000);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }"
    x-on:toast-show.window="add($event.detail)"
    class="fixed bottom-4 end-4 z-50 flex flex-col gap-ui"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            :class="{
                'bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-700': toast.dataset?.variant === 'danger',
                'bg-green-50 dark:bg-green-900/30 border-green-200 dark:border-green-700': toast.dataset?.variant === 'success',
                'bg-yellow-50 dark:bg-yellow-900/30 border-yellow-200 dark:border-yellow-700': toast.dataset?.variant === 'warning',
                'bg-white dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700': !toast.dataset?.variant || toast.dataset?.variant === 'default',
            }"
            class="flex items-start gap-3 rounded-lg border p-3 shadow-lg min-w-[18rem] max-w-sm"
        >
            <div class="flex-1 min-w-0">
                <p x-show="toast.slots?.heading" x-text="toast.slots?.heading" class="text-sm font-medium text-zinc-900 dark:text-zinc-100"></p>
                <p x-show="toast.slots?.text" x-text="toast.slots?.text" class="text-sm text-zinc-600 dark:text-zinc-400"></p>
            </div>
            <button
                type="button"
                x-on:click="remove(toast.id)"
                class="shrink-0 rounded p-0.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200"
            >
                <flux:icon.x variant="micro" />
            </button>
        </div>
    </template>
</div>
