<script setup>
import { onMounted, ref, watch } from 'vue';
import InputError from "@/Components/InputError.vue";
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";

const props = defineProps({
    modelValue: String,
    placeholder: String,
    error: String,
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

// Function to format the time value before emitting
const formatTime = (value) => {
    if (!value) return ''; // Handle empty input

    const [hours, minutes] = value.split(':'); // Extract hours and minutes
    return `${hours}:${minutes}:00`; // Append seconds as 00
};
</script>

<template>
    <div class="w-full relative">
        <input
            ref="input"
            class="border-gray-300 w-full dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-AB dark:focus:border-AB focus:ring-AB dark:focus:ring-AB rounded-md shadow-sm"
            :value="modelValue"
            :placeholder="placeholder"
            type="time"
            name="trip-start"
            @input="$emit('update:modelValue', formatTime($event.target.value))"
        />
        <transition-container type="slide-bottom">
            <InputError v-show="!!error" class="absolute -top-1 left-3" :message="error" />
        </transition-container>
    </div>
</template>
