<script setup>
import { onMounted, ref, watch } from 'vue';

const props = defineProps({
    modelValue: String,
    placeholder: String,
});

const emit = defineEmits(['update:modelValue']);

const input = ref(null);
const isChameleon = ref(props.chameleon); // Local state for toggling

onMounted(() => {
    if (input.value && input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

// Watch for changes in the prop and sync it with the local state
watch(() => props.chameleon, (newVal) => {
    isChameleon.value = newVal;
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <textarea
        ref="input"
        class="border-gray-300 dark:border-gray-700 leading-tight dark:bg-gray-900 dark:text-gray-300 focus:border-AB dark:focus:border-AB focus:ring-AB dark:focus:ring-AB rounded-md shadow-sm"
        :value="modelValue"
        :placeholder="placeholder"
        @input="$emit('update:modelValue', $event.target.value)"
    />
</template>
