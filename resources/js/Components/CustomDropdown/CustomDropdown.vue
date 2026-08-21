<template>
    <Field
        :id="id"
        :label="label"
        :error="error"
        :required="required"
        :hint="hint"
        :guide="guide"
        :clearable="false" 
        :has-value="!!selected"
        :disabled="disabled"
    >
        <template #label-icon>
            <slot name="label-icon">
                <LuList class="w-3.5 h-3.5 text-slate-400" />
            </slot>
        </template>
        
        <template v-if="$slots['header-actions']" #header-actions>
            <slot name="header-actions"></slot>
        </template>
        
        <template #default="{ inputId, isInvalid, isValid, guideId }">

        <!-- Dropdown Trigger -->
        <div class="relative w-full rounded-xl border ">
            <div
                :id="inputId"
                :class="[
                    'w-full flex gap-2 justify-between items-center rounded-xl px-3 py-2.5 transition-all duration-200',
                    'bg-transparent',
                    disabled
                        ? 'opacity-60 cursor-not-allowed'
                        : 'cursor-pointer',
                    isInvalid
                        ? ''
                        : 'focus-within:ring-0',
                ]"
                :aria-invalid="isInvalid"
                :aria-describedby="guideId"
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
                    <button
                        v-if="selected && showClear && !disabled"
                        type="button"
                        @click.stop.prevent="select(null)"
                        class="p-1 rounded-md text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                        aria-label="Clear selection"
                    >
                        <LuX class="w-4 h-4" />
                    </button>

                    <!-- Chevron -->
                    <LuChevronDown
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
                            <LuInbox class="w-6 h-6 text-slate-300 dark:text-slate-600" />
                            <span class="text-xs sm:text-sm">No options available</span>
                        </div>

                        <template v-else>
                            <!-- All Option -->
                            <dropdown-option
                                v-if="withAllOption"
                                @click.prevent="select({name: null, label: 'All'})"
                                :selected="selected && selected.name === defaultOption.name"
                            >
                                <div class="flex items-center gap-2 text-xs sm:text-sm font-semibold">
                                    <LuLayoutGrid class="w-3.5 h-3.5 text-lime-600 dark:text-lime-400" />
                                    All
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
        </template>
    </Field>
</template>

<script>
import DropdownOption from "@/Components/CustomDropdown/Components/DropdownOption.vue";
import FieldMixin from '@/Components/Forms/FieldMixin';

export default {
    mixins: [FieldMixin],
    components: {
        DropdownOption,
    },
    emits: ['selectedChange'],
    props: {
        searchable: {
            type: Boolean,
            required: false,
            default: false,
        },
        withAllOption: {
            type: Boolean,
            required: false,
            default: true,
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
    },
    data() {
        return {
            open: false,
            defaultOption: { name: null, label: 'All', selected: true },
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
                    const selectedOption = this.options?.find(option => option.name === this.value);
                    if (selectedOption) {
                        this.selected = selectedOption;
                        this.filteredOptions = [selectedOption, ...this.options.filter(option => option.name !== this.value)];
                        return;
                    }
                }
                this.selected = this.options?.find(option => option.selected) || null;
                this.filteredOptions = this.options || [];
            },
            deep: true,
        },
        'value': {
            handler(newVal) {
                const opts = Array.isArray(this.options) ? this.options : [];
                if (newVal !== undefined && newVal !== null) {
                    const selectedOption = opts.find(option => option.name === this.value);
                    if (selectedOption) {
                        this.selected = selectedOption;
                        this.filteredOptions = [selectedOption, ...opts.filter(option => option.name !== newVal)];
                        return;
                    }
                } else {
                    this.selected = opts.find(option => option.selected) || null;
                    this.filteredOptions = opts;
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
