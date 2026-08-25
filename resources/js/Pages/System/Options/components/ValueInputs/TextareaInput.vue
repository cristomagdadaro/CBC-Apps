<template>
    <div class="relative">
        <textarea
            :value="modelValue"
            @input="handleInput"
            @focus="showOptions = true"
            @blur="hideOptions"
            rows="5"
            placeholder="Enter text value"
            class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white dark:bg-slate-900"></textarea>

        <!-- Custom Dropdown Menu -->
        <ul
            v-if="showOptions && filteredOptions.length"
            class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto">
            <li
                v-for="option in filteredOptions"
                :key="option.value"
                @mousedown.prevent="selectOption(option.value)"
                class="px-4 py-2 cursor-pointer hover:bg-blue-500 hover:text-white">
                {{ option.label }}
            </li>
        </ul>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: "",
    },
    datalistOptions: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["update:modelValue"]);

const showOptions = ref(false);

// Filter options based on what the user has typed
const filteredOptions = computed(() => {
    if (!props.modelValue) return props.datalistOptions;
    const lowercasedInput = String(props.modelValue).toLowerCase();
    return props.datalistOptions.filter((opt) => String(opt.value).toLowerCase().includes(lowercasedInput) || String(opt.label).toLowerCase().includes(lowercasedInput));
});

const handleInput = (event) => {
    emit("update:modelValue", event.target.value);
    showOptions.value = true;
};

const selectOption = (value) => {
    emit("update:modelValue", value);
    showOptions.value = false;
};

// Delay hiding the options to allow the click event to fire on the list items
const hideOptions = () => {
    setTimeout(() => {
        showOptions.value = false;
    }, 150);
};
</script>
