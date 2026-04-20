<template>
    <div
        :class="[
            'duration-200 text-sm whitespace-normal break-words overflow-hidden w-full px-3 py-2 cursor-pointer flex items-center gap-2',
            selected
                ? 'bg-AA/10 dark:bg-AA/20 text-AA font-medium'
                : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 active:bg-gray-100 dark:active:bg-gray-700'
        ]"
    >
        <Check v-if="selected" class="w-4 h-4 flex-shrink-0" />
        <div v-else class="w-4 h-4 flex-shrink-0" />
        <slot />
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
