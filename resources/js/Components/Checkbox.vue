<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Boolean, String, Number],
        default: false,
    },
    value: {
        type: String,
        default: null,
    },
    id: String,
});

// Normalize various boolean-like values to actual boolean
const normalizeValue = (val) => {
    if (val === null || val === undefined || val === '') {
        return false;
    }
    if (typeof val === 'boolean') {
        return val;
    }
    if (typeof val === 'string') {
        const lower = val.toLowerCase().trim();
        return lower === 'true' || lower === '1' || lower === 'yes';
    }
    if (typeof val === 'number') {
        return val !== 0;
    }
    return Boolean(val);
};

const proxyChecked = computed({
    get() {
        return normalizeValue(props.checked);
    },

    set(val) {
        emit('update:checked', val);
    },
});
</script>


<template>
    <input
        :id="id"
        :name="id"
        v-model="proxyChecked"
        type="checkbox"
        :value="value"
        class="rounded leading-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-AB dark:focus:border-AB focus:ring-AB dark:focus:ring-AB "
    >
</template>
