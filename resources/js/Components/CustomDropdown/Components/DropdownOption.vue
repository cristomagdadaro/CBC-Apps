<template>
    <div
        :class="[
            'duration-200 text-xs sm:text-sm whitespace-normal break-words overflow-hidden w-full px-3 py-2 cursor-pointer flex items-center justify-between gap-2',
            selected
                ? 'bg-lime-50 dark:bg-lime-950/40 text-lime-700 dark:text-lime-300 font-semibold'
                : 'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/60 active:bg-slate-100 dark:active:bg-slate-800'
        ]"
    >   <slot />
        <Check v-if="selected" class="w-4 h-4 text-lime-600 dark:text-lime-400 flex-shrink-0" />
    </div>
</template>

<script>
import { Check } from 'lucide-vue-next';

export default {
    components: { Check },
    props: {
        selected: {
            type: Boolean,
            required: false,
            default: false,
        }
    },
}
</script>
