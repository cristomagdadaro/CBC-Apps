<script setup>
import { onMounted, ref, watch } from 'vue';
import InputError from "@/Components/InputError.vue";
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";

const props = defineProps({
    modelValue: [String, Number],
    autocomplete: String,
    placeholder: String,
    error: String,
    type: String,
    classes: String,
    id: String,
    label: String,
    required: Boolean,
    typeInput: String,
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
    <div class="w-full relative" :class="classes">
        <div v-if="label" class="text-xs text-gray-700 flex items-center justify-between">
            <span class="flex gap-0.5 whitespace-nowrap">{{ label }} <b v-if="required" class="text-red-500 ">*</b></span>
            <transition-container type="slide-bottom">
                <InputError v-show="!!error" class="" :message="error" />
            </transition-container>
        </div>
        <input
            :id="id"
            :name="id"
            ref="input"
            :class="{'border-red-500': error}"
            :autocomplete="autocomplete"
            class="w-full placeholder:text-gray-300 focus:border-AB focus:ring-AB rounded-md shadow-sm"
            :value="modelValue"
            :placeholder="placeholder"
            :type="typeInput || type"
            @input="$emit('update:modelValue', $event.target.value)"
        >
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
