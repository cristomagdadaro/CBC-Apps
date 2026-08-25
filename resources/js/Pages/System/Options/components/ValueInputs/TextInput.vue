<template>
    <input
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        type="text"
        placeholder="Enter text value"
        class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white dark:bg-slate-900" />
    <datalist
        v-if="datalistOptions.length"
        :id="`datalist-${_uid}`">
        <option
            v-for="option in datalistOptions"
            :key="option.value"
            :value="option.value">
            {{ option.label }}
        </option>
    </datalist>
</template>

<script setup>
defineProps({
    modelValue: {
        type: [String, Number],
        default: "",
    },
    datalistOptions: {
        type: Array,
        default: () => [],
    },
    type: {
        type: String,
        default: "text",
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

defineEmits(["update:modelValue"]);
</script>
