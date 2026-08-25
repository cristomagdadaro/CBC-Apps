<template>
    <custom-dropdown
        :value="normalizedValue"
        @selectedChange="$emit('update:modelValue', $event)"
        :options="booleanOptions"
        :withAllOption="false"
        class="w-full" />
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    modelValue: {
        type: [String, Boolean],
        default: "false",
    },
});

defineEmits(["update:modelValue"]);

const normalizedValue = computed(() => {
    return props.modelValue === "true" || props.modelValue === true || props.modelValue === "1" ? "true" : "false";
});

const booleanOptions = [
    { name: "true", label: "True", value: "true" },
    { name: "false", label: "False", value: "false" },
];
</script>
