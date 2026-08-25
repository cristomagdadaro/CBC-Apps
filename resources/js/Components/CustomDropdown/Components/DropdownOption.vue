<template>
    <div :class="['flex w-full cursor-pointer items-center justify-between gap-2 overflow-hidden whitespace-normal break-words px-3 py-2 text-xs duration-200 sm:text-sm', selected ? 'bg-lime-50 font-semibold text-lime-700 dark:bg-lime-950/40 dark:text-lime-300' : 'text-slate-700 hover:bg-slate-50 active:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800/60 dark:active:bg-slate-800']">
        <slot />
        <Check
            v-if="selected"
            class="h-4 w-4 flex-shrink-0 text-lime-600 dark:text-lime-400" />
    </div>
</template>

<script>
import { Check } from "lucide-vue-next";

export default {
    components: { Check },
    props: {
        selected: {
            type: Boolean,
            required: false,
            default: false,
        },
    },
};
</script>
