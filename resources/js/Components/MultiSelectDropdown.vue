<template>
    <div class="flex flex-col gap-0.5 w-full rounded-md">
        <div v-if="label" class="text-xs text-gray-500 flex items-center justify-between">
            <span class="flex gap-0.5 whitespace-nowrap">{{ label }}<b v-if="required" class="text-red-500">*</b></span>
            <transition-container type="slide-bottom">
                <InputError v-show="!!error" class="" :message="error" />
            </transition-container>
        </div>
        <div class="relative">
            <div
                :class="[
                    'w-full flex gap-2 flex-wrap justify-between items-start border-gray-700 rounded px-4 py-2 border',
                    { 'bg-white': !disabled, 'bg-gray-100': disabled, 'opacity-60 cursor-not-allowed': disabled, 'border-red-600': !!error }
                ]"
                @click.prevent="toggle"
            >
                <div class="flex gap-2 flex-wrap items-center flex-1">
                    <div v-if="selectedOptions.length === 0" class="text-gray-400">{{ placeholder }}</div>
                    <div v-for="option in selectedOptions" :key="option.value" class="flex gap-1 items-center bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">
                        <span>{{ option.label }}</span>
                        <button
                            v-if="!disabled"
                            type="button"
                            @click.stop.prevent="removeOption(option.value)"
                            class="text-blue-600 hover:text-blue-900 font-bold"
                        >
                            ×
                        </button>
                    </div>
                </div>
                <div class="flex gap-2 items-center flex-shrink-0 my-auto">
                    <close-icon
                        v-if="selectedOptions.length > 0 && showClear && !disabled"
                        class="h-5 w-5 cursor-pointer"
                        @click.stop.prevent="clearAll"
                    />
                    <slot v-if="!disabled" name="icon" :class="open ? 'rotate-180' : 'rotate-360'" class="h-4 w-4 duration-300" />
                </div>
            </div>

            <div v-show="open" class="fixed inset-0 z-48" @click.prevent="open = false" />

            <transition-container>
                <div
                    v-show="open"
                    class="z-50 absolute border shadow rounded bg-white mt-1 max-h-[40vh] overflow-hidden overflow-y-auto w-full"
                >
                    <div v-if="options.length === 0" class="text-center text-gray-500 px-4 py-2">
                        No options available
                    </div>
                    <template v-else>
                        <div class="sticky top-0 bg-white px-4 py-2 border-b">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search options..."
                                class="w-full border rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                @keydown.esc="search = ''"
                            />
                        </div>
                        <div v-if="filteredOptions.length === 0" class="text-center text-gray-500 px-4 py-2">
                            No matching options
                        </div>
                        <label v-for="(option, index) in filteredOptions" :key="`${option.value}-${index}`" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-100 cursor-pointer">
                            <input
                                type="checkbox"
                                :checked="isSelected(option.value)"
                                @change="toggleOption(option.value)"
                                class="rounded"
                            />
                            <span class="text-gray-700">{{ option.label }}</span>
                        </label>
                    </template>
                </div>
            </transition-container>
        </div>
    </div>
</template>

<script>
import CloseIcon from "@/Components/Icons/CloseIcon.vue";

export default {
    name: "MultiSelectDropdown",
    components: { CloseIcon },
    emits: ['update:modelValue'],
    props: {
        modelValue: {
            type: Array,
            default: () => [],
        },
        label: {
            type: String,
            required: false,
        },
        placeholder: {
            type: String,
            default: "Select options...",
        },
        options: {
            type: Array,
            default: () => [],
        },
        showClear: {
            type: Boolean,
            default: true,
        },
        required: {
            type: Boolean,
            default: false,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        error: String,
    },
    data() {
        return {
            open: false,
            search: "",
            selectedValues: [],
        };
    },
    computed: {
        filteredOptions() {
            if (!this.search || !this.search.trim()) return this.options;
            
            // Normalize the search term: lowercase, trim, normalize whitespace
            const searchTerms = this.search
                .toLowerCase()
                .trim()
                .split(/\s+/)
                .filter(t => t.length > 0);
            
            if (searchTerms.length === 0) return this.options;
            
            return this.options.filter(opt => {
                const label = opt.label.toLowerCase();
                // Match if ALL search terms are found in the label (case-insensitive)
                return searchTerms.every(term => label.includes(term));
            });
        },
        selectedOptions() {
            return this.options.filter(opt => this.selectedValues.includes(opt.value));
        },
    },
    watch: {
        modelValue(newVal) {
            this.selectedValues = Array.isArray(newVal) ? [...newVal] : [];
        },
    },
    methods: {
        toggle() {
            if (this.disabled) return;
            this.open = !this.open;
        },
        isSelected(value) {
            return this.selectedValues.includes(value);
        },
        toggleOption(value) {
            if (this.isSelected(value)) {
                this.removeOption(value);
            } else {
                this.addOption(value);
            }
        },
        addOption(value) {
            if (!this.selectedValues.includes(value)) {
                this.selectedValues.push(value);
                this.$emit('update:modelValue', [...this.selectedValues]);
            }
        },
        removeOption(value) {
            const index = this.selectedValues.indexOf(value);
            if (index > -1) {
                this.selectedValues.splice(index, 1);
                this.$emit('update:modelValue', [...this.selectedValues]);
            }
        },
        clearAll() {
            this.selectedValues = [];
            this.$emit('update:modelValue', []);
        },
    },
    mounted() {
        this.selectedValues = Array.isArray(this.modelValue) ? [...this.modelValue] : [];
    },
};
</script>
