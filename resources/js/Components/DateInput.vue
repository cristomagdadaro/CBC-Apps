<script setup>
import { onMounted, ref, watch } from 'vue';
import InputError from "@/Components/InputError.vue";
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";

const props = defineProps({
    modelValue: String,
    placeholder: String,
    error: String,
    label: String,
    required: Boolean,
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
    <div class="w-full relative border-none p-0" :class="{'border-red-500': error}">
        <div v-if="label" class="text-xs text-gray-500 flex items-center justify-between">
            <span class="flex gap-0.5 whitespace-nowrap">{{ label }}<b v-if="required" class="text-red-500 ">*</b></span>
            <transition-container type="slide-bottom">
                <InputError v-show="!!error" :message="error" />
            </transition-container>
        </div>
        <input
            ref="input"
            class="border w-full dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-AB dark:focus:border-AB focus:ring-AB dark:focus:ring-AB rounded-md shadow-sm"
            :value="modelValue"
            :placeholder="placeholder"
            type="date"
            name="trip-start"
            @input="$emit('update:modelValue', $event.target.value)"
        />
    </div>
</template>
