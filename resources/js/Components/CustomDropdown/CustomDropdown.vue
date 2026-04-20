<template>
    <div class="flex flex-col gap-1.5 w-full">
        <!-- Label Row -->
        <div v-if="label" class="flex items-center justify-between">
            <label class="text-xs font-medium text-gray-700 dark:text-gray-200 flex items-center gap-1">
                <List class="w-3.5 h-3.5 text-gray-400" />
                <span class="flex items-center gap-0.5">
                    {{ label }}
                    <span v-if="required" class="text-red-500">*</span>
                </span>
            </label>
            <transition-container type="slide-bottom">
                <div v-if="error" class="flex items-center gap-1 text-xs text-red-600 dark:text-red-400">
                    <AlertCircle class="w-3.5 h-3.5" />
                    <span>{{ error }}</span>
                </div>
            </transition-container>
        </div>

        <!-- Dropdown Trigger -->
        <div class="relative">
            <div
                :class="[
                    'w-full flex gap-2 justify-between items-center rounded-lg border px-3 py-2.5 transition-all duration-200',
                    'bg-white dark:bg-gray-800',
                    disabled
                        ? 'bg-gray-100 dark:bg-gray-700 opacity-60 cursor-not-allowed'
                        : 'cursor-pointer hover:border-gray-400 dark:hover:border-gray-500',
                    error
                        ? 'border-red-300 dark:border-red-700 focus-within:border-red-500 focus-within:ring-2 focus-within:ring-red-200 dark:focus-within:ring-red-900'
                        : 'border-gray-300 dark:border-gray-600 focus-within:border-AA focus-within:ring-2 focus-within:ring-AA/20',
                ]"
                @click.prevent="toggle"
            >
                <!-- Non-searchable Display -->
                <div
                    v-if="!searchable"
                    :class="[
                        'text-sm whitespace-nowrap overflow-hidden text-ellipsis flex-1',
                        selected ? 'text-gray-900 dark:text-gray-100' : 'text-gray-400 dark:text-gray-500',
                        { 'text-gray-400 dark:text-gray-500': disabled }
                    ]"
                >
                    {{ selected ? selected.label : value ? value : placeholder }}
                </div>

                <!-- Searchable Input -->
                <input
                    v-else
                    ref="searchInput"
                    type="text"
                    v-model="search"
                    @keydown.esc="search = null"
                    @input="filterOptions"
                    class="w-full text-sm text-gray-900 dark:text-gray-100 bg-transparent border-none focus:outline-none focus:ring-0 p-0 placeholder:text-gray-400 dark:placeholder:text-gray-500"
                    :placeholder="selected ? selected.label : placeholder"
                />

                <!-- Actions -->
                <div class="flex gap-1.5 items-center flex-shrink-0">
                    <!-- Clear Button -->
                    <button
                        v-if="selected && showClear && !disabled"
                        type="button"
                        @click.stop.prevent="select(null)"
                        class="p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        aria-label="Clear selection"
                    >
                        <X class="w-4 h-4" />
                    </button>

                    <!-- Chevron -->
                    <ChevronDown
                        v-if="!disabled"
                        :class="[
                            'w-4 h-4 text-gray-400 transition-transform duration-300',
                            open ? 'rotate-180' : ''
                        ]"
                    />
                </div>
            </div>

            <!-- Backdrop -->
            <div v-show="open" class="fixed inset-0 z-40" @click.prevent="open = false" />

            <!-- Dropdown Menu -->
            <transition-container type="fade">
                <div
                    v-show="open"
                    class="z-50 absolute mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-[30vh] overflow-hidden flex flex-col"
                >
                    <!-- Options List -->
                    <div class="overflow-y-auto flex-1 py-1">
                        <!-- No Options -->
                        <div v-if="!filteredOptions.length" class="flex flex-col items-center justify-center gap-2 px-4 py-6 text-gray-500 dark:text-gray-400">
                            <Inbox class="w-6 h-6 text-gray-300 dark:text-gray-600" />
                            <span class="text-sm">No options available</span>
                        </div>

                        <template v-else>
                            <!-- All Option -->
                            <dropdown-option
                                v-if="withAllOption"
                                @click.prevent="select({name: null, label: 'All fields'})"
                                :selected="selected && selected.name === defaultOption.name"
                            >
                                <div class="flex items-center gap-2">
                                    <LayoutGrid class="w-3.5 h-3.5" />
                                    All fields
                                </div>
                            </dropdown-option>

                            <!-- Options -->
                            <dropdown-option
                                v-for="option in filteredOptions"
                                :key="option.label + option.name"
                                @click.prevent="select(option)"
                                :selected="option.name === value"
                            >
                                {{ option.label }}
                            </dropdown-option>
                        </template>
                    </div>
                </div>
            </transition-container>
        </div>

        <!-- Guide Text -->
        <p v-if="guide" class="text-xs text-gray-500 dark:text-gray-400 flex items-start gap-1">
            <HelpCircle class="w-3 h-3 mt-0.5 flex-shrink-0" />
            <span>{{ guide }}</span>
        </p>
    </div>
</template>

<script>
import DropdownOption from "@/Components/CustomDropdown/Components/DropdownOption.vue";
import {
    ChevronDown,
    X,
    AlertCircle,
    List,
    Inbox,
    LayoutGrid,
    HelpCircle
} from 'lucide-vue-next';

export default {
    components: {
        DropdownOption,
        ChevronDown,
        X,
        AlertCircle,
        List,
        Inbox,
        LayoutGrid,
        HelpCircle
    },
    props: {
        searchable: {
            type: Boolean,
            required: false,
            default: false,
        },
        label: {
            type: String,
            required: false,
        },
        withAllOption: {
            type: Boolean,
            required: false,
            default: true,
        },
        placeholder: {
            type: String,
            required: false,
        },
        options: {
            type: Object,
            required: false,
        },
        value: {
            type: [String, Number],
            required: false,
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
        guide: { type: String, default: null },
    },
    data() {
        return {
            open: false,
            defaultOption: { name: null, label: 'All fields', selected: true },
            selected: null,
            search: null,
            filteredOptions: [],
        }
    },
    methods: {
        toggle() {
            if (this.disabled) return;
            this.open = !this.open;
            if (this.open && this.searchable) {
                this.$nextTick(() => {
                    this.$refs.searchInput?.focus();
                });
            }
        },
        select(option) {
            if (this.disabled) return;
            if (option) {
                this.$emit('selectedChange', option.name);
            } else {
                this.$emit('selectedChange', null);
            }
            this.search = option ? option.label : null;
            this.selected = option;
            this.open = false;
        },
        selectByValue(value, silent = false) {
            this.selected = this.options.find(option => option.name === value);
            if (!silent) {
                if (this.disabled) return;
                this.$emit('selectedChange', this.selected ? this.selected.name : null);
            }
        },
        filterOptions() {
            if (this.search)
                this.filteredOptions = this.options.filter(option =>
                    option.label.toLowerCase().includes(this.search.toLowerCase())
                );
            else
                this.filteredOptions = this.options;
        }
    },
    watch: {
        'options': {
            handler() {
                if (this.value !== undefined && this.value !== null) {
                    const selectedOption = this.options.find(option => option.name === this.value);
                    if (selectedOption) {
                        this.selected = selectedOption;
                        this.filteredOptions = [selectedOption, ...this.options.filter(option => option.name !== this.value)];
                        return;
                    }
                }
                this.selected = this.options.find(option => option.selected) || null;
                this.filteredOptions = this.options;
            },
            deep: true,
        },
        'value': {
            handler(newVal) {
                if (newVal !== undefined && newVal !== null) {
                    const selectedOption = this.options.find(option => option.name === this.value);
                    if (selectedOption) {
                        this.selected = selectedOption;
                        this.filteredOptions = [selectedOption, ...this.options.filter(option => option.name !== newVal)];
                        return;
                    }
                } else {
                    this.selected = this.options.find(option => option.selected) || null;
                    this.filteredOptions = this.options;
                }
            },
            immediate: true
        },
    },
    mounted() {
        if (!this.options) return;
        if (this.value !== undefined && this.value !== null) {
            const selectedOption = this.options.find(option => option.name === this.value);
            if (selectedOption) {
                this.selected = selectedOption;
                this.filteredOptions = [selectedOption, ...this.options.filter(option => option.name !== this.value)];
                return;
            }
        }
        this.selected = this.options.find(option => option.selected) || null;
        this.filteredOptions = this.options;
    }
}
</script>

<style scoped>
/* Scrollbar styling */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 3px;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(75, 85, 99, 0.5);
}
</style>
