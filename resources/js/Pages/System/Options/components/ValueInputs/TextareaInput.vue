<template>
    <textarea
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        rows="5"
        placeholder="Enter text value"
        class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
    ></textarea>
    <datalist v-if="datalistOptions.length" :id="`datalist-${_uid}`">
        <option
            v-for="option in datalistOptions"
            :key="option.value"
            :value="option.value"
        >
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
});

defineEmits(["update:modelValue"]);
</script>
