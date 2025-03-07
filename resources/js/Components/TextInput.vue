<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: String,
    placeholder: String,
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        ref="input"
        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-AB dark:focus:border-AB focus:ring-AB dark:focus:ring-AB rounded-md shadow-sm"
        :value="modelValue"
        :placeholder="placeholder"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template>
