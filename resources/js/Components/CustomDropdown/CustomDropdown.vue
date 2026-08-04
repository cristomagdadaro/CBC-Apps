<template>
    <div class="flex flex-col gap-1.5 w-full">
        <!-- Label Row -->
        <div v-if="label" class="flex items-center justify-between">
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1">
                <List class="w-3.5 h-3.5 text-slate-400" />
                <span class="flex items-center gap-0.5">
                    {{ label }}
                    <span v-if="required" class="text-rose-500">*</span>
                </span>
            </label>
            <transition-container type="slide-bottom">
                <div v-if="error" class="flex items-center gap-1 text-xs text-rose-600 dark:text-rose-400 font-medium">
                    <AlertCircle class="w-3.5 h-3.5" />
                    <span>{{ error }}</span>
                </div>
            </transition-container>
        </div>

        <!-- Dropdown Trigger -->
        <div class="relative">
            <div
                :class="[
                    'w-full flex gap-2 justify-between items-center rounded-xl border px-3 py-2.5 transition-all duration-200 shadow-xs',
                    'bg-white dark:bg-slate-900',
                    disabled
                        ? 'bg-slate-100 dark:bg-slate-800 opacity-60 cursor-not-allowed'
                        : 'cursor-pointer hover:border-slate-400 dark:hover:border-slate-600',
                    error
                        ? 'border-rose-300 dark:border-rose-700 focus-within:border-rose-500 focus-within:ring-2 focus-within:ring-rose-200 dark:focus-within:ring-rose-900'
                        : 'border-slate-200 dark:border-slate-700 focus-within:border-lime-500 focus-within:ring-2 focus-within:ring-lime-500/20',
                ]"
                @click.prevent="toggle"
            >
                <!-- Non-searchable Display -->
                <div
                    v-if="!searchable"
                    :class="[
                        'text-xs sm:text-sm whitespace-nowrap overflow-hidden text-ellipsis flex-1',
                        selected ? 'text-slate-900 dark:text-slate-100 font-medium' : 'text-slate-400 dark:text-slate-500',
                        { 'text-slate-400 dark:text-slate-500': disabled }
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
                    class="w-full text-xs sm:text-sm text-slate-900 dark:text-slate-100 bg-transparent border-none focus:outline-none focus:ring-0 p-0 placeholder:text-slate-400 dark:placeholder:text-slate-500"
                    :placeholder="selected ? selected.label : placeholder"
                />

                <!-- Actions -->
                <div class="flex gap-1.5 items-center flex-shrink-0">
                    <!-- Clear Button -->
                    <button
                        v-if="selected && showClear && !disabled"
                        type="button"
                        @click.stop.prevent="select(null)"
                        class="p-1 rounded-md text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                        aria-label="Clear selection"
                    >
                        <X class="w-4 h-4" />
                    </button>

                    <!-- Chevron -->
                    <ChevronDown
                        v-if="!disabled"
                        :class="[
                            'w-4 h-4 text-slate-400 transition-transform duration-300',
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
                    class="z-50 absolute mt-1.5 w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-lg max-h-[30vh] overflow-hidden flex flex-col"
                >
                    <!-- Options List -->
                    <div class="overflow-y-auto flex-1 py-1">
                        <!-- No Options -->
                        <div v-if="!filteredOptions.length" class="flex flex-col items-center justify-center gap-2 px-4 py-6 text-slate-500 dark:text-slate-400">
                            <Inbox class="w-6 h-6 text-slate-300 dark:text-slate-600" />
                            <span class="text-xs sm:text-sm">No options available</span>
                        </div>

                        <template v-else>
                            <!-- All Option -->
                            <dropdown-option
                                v-if="withAllOption"
                                @click.prevent="select({name: null, label: 'All fields'})"
                                :selected="selected && selected.name === defaultOption.name"
                            >
                                <div class="flex items-center gap-2 text-xs sm:text-sm font-semibold">
                                    <LayoutGrid class="w-3.5 h-3.5 text-lime-600 dark:text-lime-400" />
                                    All fields
                                </div>
                            </dropdown-option>

                            <!-- Options -->
                            <dropdown-option
                                v-for="(option, index) in filteredOptions"
                                :key="'opt-' + index + '-' + (option?.name ?? option?.label ?? '')"
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
        <p v-if="guide" class="text-xs text-slate-500 dark:text-slate-400 flex items-start gap-1">
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
