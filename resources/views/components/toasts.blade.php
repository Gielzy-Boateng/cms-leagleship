@props(['message' => null])

<div x-data="{
        toasts: @js($message ? [['message' => $message, 'type' => 'success', 'id' => 1]] : []),
        nextId: 2,
        push(detail) {
            const id = this.nextId++;
            const type = detail?.type === 'error' ? 'error' : 'success';

            this.toasts.push({ message: detail?.message ?? String(detail), type, id });
            setTimeout(() => this.remove(id), type === 'error' ? 6000 : 4000);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        },
     }"
     x-on:toast.window="push($event.detail)"
     aria-live="polite"
     class="pointer-events-none fixed bottom-6 right-6 z-50 flex w-80 flex-col gap-2">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-transition.duration.250ms
             class="pointer-events-auto flex items-center gap-3 rounded-xl bg-navy-900 px-4 py-3 text-sm text-white shadow-lg ring-1 ring-navy-700">
            <span class="material-symbols-outlined text-[20px]"
                  :class="toast.type === 'error' ? 'text-red-400' : 'text-gold-400'"
                  x-text="toast.type === 'error' ? 'error' : 'check_circle'"></span>
            <span class="flex-1" x-text="toast.message"></span>
            <button type="button" @click="remove(toast.id)" aria-label="Dismiss notification"
                    class="rounded-md p-1 text-white/50 transition hover:bg-white/10 hover:text-white">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    </template>
</div>
