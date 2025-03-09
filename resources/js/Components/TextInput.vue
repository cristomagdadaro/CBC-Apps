<script setup>
import { onMounted, ref, watch } from 'vue';
import InputError from "@/Components/InputError.vue";
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";

const props = defineProps({
    modelValue: [String, Number],
    placeholder: String,
    error: String,
    type: String,
    classes: String,
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
    <div class="w-full relative " :class="{'border-red-500': error}">
        <input
            ref="input"
            :class="classes"
            class=" w-full dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-AB dark:focus:border-AB focus:ring-AB dark:focus:ring-AB rounded-md shadow-sm"
            :value="modelValue"
            :placeholder="placeholder"
            :type="type"
            @input="$emit('update:modelValue', $event.target.value)"
        >
        <transition-container type="slide-bottom">
            <InputError v-show="!!error" class="absolute -top-1 left-3" :message="error" />
        </transition-container>
    </div>
</template>
<style scoped>
/* Hide the number input spinners */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield; /* Hide in Firefox */
}
</style>
