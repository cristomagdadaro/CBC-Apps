<script setup>
import { computed } from 'vue';

const props = defineProps({
    /**
     * Tabs array: [{ key: string|number, label: string, icon?: component, disabled?: boolean }]
     */
    tabs: {
        type: Array,
        required: true,
    },
    /**
     * Currently active tab key.
     */
    modelValue: {
        type: [String, Number, null],
        default: null,
    },
    /**
     * Optional size variant: 'sm' | 'md'
     */
    size: {
        type: String,
        default: 'md',
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

const activeKey = computed({
    get: () => props.modelValue ?? props.tabs[0]?.key ?? null,
    set: (val) => {
        emit('update:modelValue', val);
        emit('change', val);
    },
});

const baseClasses = computed(() =>
    props.size === 'sm'
        ? 'px-3 py-1 text-xs'
        : 'px-4 py-2 text-sm'
);

const onTabClick = (tab) => {
    if (tab.disabled) return;
    activeKey.value = tab.key;
};
</script>

<template>
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="flex space-x-4" aria-label="Tabs">
            <button
                v-for="tab in tabs"
                :key="tab.key"
                type="button"
                @click="onTabClick(tab)"
                :class="[
                    baseClasses,
                    'rounded-t-md inline-flex items-center gap-1 border-b-2 font-medium focus:outline-none transition-colors',
                    tab.disabled
                        ? 'text-gray-400 dark:text-gray-500 cursor-not-allowed border-transparent'
                        : activeKey === tab.key
                            ? 'text-blue-600 dark:text-blue-400 border-blue-500 bg-blue-50/60 dark:bg-gray-800'
                            : 'text-gray-500 dark:text-gray-400 border-transparent hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800/40',
                ]"
            >
                <slot name="icon" :tab="tab" v-if="tab.icon">
                    <component :is="tab.icon" class="w-4 h-4" />
                </slot>
                <span>{{ tab.label }}</span>
            </button>
        </nav>
        <div>
            <slot :active-key="activeKey" />
        </div>
    </div>
</template>

