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
        :disabled="disabled">
        <template #label-icon>
            <slot name="label-icon">
                <LuList class="h-3.5 w-3.5 text-slate-400" />
            </slot>
        </template>

        <template
            v-if="$slots['header-actions']"
            #header-actions>
            <slot name="header-actions"></slot>
        </template>

        <template #default="{ inputId, isInvalid, isValid, guideId }">
            <!-- Dropdown Trigger -->
            <div :class="['relative rounded-xl border', !showSelectedOption ? 'inline-block w-auto' : 'w-full']">
                <div
                    :id="inputId"
                    :class="['flex items-center justify-between gap-2 rounded-xl px-3 py-2.5 transition-all duration-200', !showSelectedOption ? 'w-auto' : 'w-full', 'bg-white dark:bg-slate-900 dark:text-slate-100', disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer', isInvalid ? '' : 'focus-within:ring-0']"
                    :aria-invalid="isInvalid"
                    :aria-describedby="guideId"
                    @click.prevent="toggle">
                    <template v-if="showSelectedOption">
                        <!-- Non-searchable Display -->
                        <div
                            v-if="!searchable"
                            :class="['flex-1 overflow-hidden text-ellipsis whitespace-nowrap text-xs sm:text-sm', selected ? 'font-medium text-slate-900 dark:text-slate-100' : 'text-slate-400 dark:text-slate-500', { 'text-slate-400 dark:text-slate-500': disabled }]">
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
                            class="w-full border-none bg-transparent p-0 text-xs text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0 sm:text-sm dark:text-slate-100 dark:placeholder:text-slate-500"
                            :placeholder="selected ? selected.label : placeholder" />
                    </template>
                    <template v-else>
                        <div :class="['flex items-center', !showSelectedOption ? 'flex-none' : 'flex-1']">
                            <slot name="trigger"></slot>
                        </div>
                    </template>

                    <!-- Actions -->
                    <div class="flex flex-shrink-0 items-center gap-1.5">
                        <button
                            v-if="selected && showClear && !disabled"
                            type="button"
                            @click.stop.prevent="select(null)"
                            class="rounded-md p-1 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
                            aria-label="Clear selection">
                            <LuX class="h-4 w-4" />
                        </button>

                        <!-- Chevron -->
                        <LuChevronDown
                            v-if="!disabled"
                            :class="['h-4 w-4 text-slate-400 transition-transform duration-300', open ? 'rotate-180' : '']" />
                    </div>
                </div>

                <!-- Backdrop -->
                <div
                    v-show="open"
                    class="fixed inset-0 z-40"
                    @click.prevent="open = false" />

                <!-- Dropdown Menu -->
                <transition-container type="fade">
                    <div
                        v-show="open"
                        class="absolute z-50 mt-1.5 flex max-h-[30vh] min-w-full w-max flex-col overflow-visible rounded-xl border border-slate-200 bg-white shadow-lg dark:border-slate-800 dark:bg-slate-900">
                        <!-- Options List -->
                        <div class="flex-1 overflow-visible overflow-y-auto py-1">
                            <!-- No Options -->
                            <div
                                v-if="!filteredOptions.length"
                                class="flex flex-col items-center justify-center gap-2 px-4 py-6 text-slate-500 dark:text-slate-400">
                                <LuInbox class="h-6 w-6 text-slate-300 dark:text-slate-600" />
                                <span class="text-xs sm:text-sm">No options available</span>
                            </div>

                            <template v-else>
                                <!-- All Option -->
                                <dropdown-option
                                    v-if="withAllOption"
                                    @click.prevent="select({ name: null, label: 'All' })"
                                    :selected="selected && selected.name === defaultOption.name">
                                    <div class="flex items-center gap-2 text-xs font-semibold sm:text-sm">
                                        <LuLayoutGrid class="h-3.5 w-3.5 text-lime-600 dark:text-lime-400" />
                                        All
                                    </div>
                                </dropdown-option>

                                <!-- Options -->
                                <dropdown-option
                                    v-for="(option, index) in filteredOptions"
                                    :key="'opt-' + index + '-' + (option?.name ?? option?.label ?? '')"
                                    @click.prevent="select(option)"
                                    :selected="option.name === value">
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
import FieldMixin from "@/Components/Forms/FieldMixin";

export default {
    mixins: [FieldMixin],
    components: {
        DropdownOption,
    },
    emits: ["selectedChange"],
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
        showSelectedOption: {
            type: Boolean,
            default: true,
        },
    },
    data() {
        return {
            open: false,
            defaultOption: { name: null, label: "All", selected: true },
            selected: null,
            search: null,
            filteredOptions: [],
        };
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
                this.$emit("selectedChange", option.name);
            } else {
                this.$emit("selectedChange", null);
            }
            this.search = option ? option.label : null;
            this.selected = option;
            this.open = false;
        },
        selectByValue(value, silent = false) {
            this.selected = this.options.find((option) => option.name === value);
            if (!silent) {
                if (this.disabled) return;
                this.$emit("selectedChange", this.selected ? this.selected.name : null);
            }
        },
        filterOptions() {
            if (this.search) this.filteredOptions = this.options.filter((option) => option.label.toLowerCase().includes(this.search.toLowerCase()));
            else this.filteredOptions = this.options;
        },
    },
    watch: {
        options: {
            handler() {
                if (this.value !== undefined && this.value !== null) {
                    const selectedOption = this.options?.find((option) => option.name === this.value);
                    if (selectedOption) {
                        this.selected = selectedOption;
                        this.filteredOptions = [selectedOption, ...this.options.filter((option) => option.name !== this.value)];
                        return;
                    }
                }
                this.selected = this.options?.find((option) => option.selected) || null;
                this.filteredOptions = this.options || [];
            },
            deep: true,
        },
        value: {
            handler(newVal) {
                const opts = Array.isArray(this.options) ? this.options : [];
                if (newVal !== undefined && newVal !== null) {
                    const selectedOption = opts.find((option) => option.name === this.value);
                    if (selectedOption) {
                        this.selected = selectedOption;
                        this.filteredOptions = [selectedOption, ...opts.filter((option) => option.name !== newVal)];
                        return;
                    }
                } else {
                    this.selected = opts.find((option) => option.selected) || null;
                    this.filteredOptions = opts;
                }
            },
            immediate: true,
        },
    },
    mounted() {
        if (!this.options) return;
        if (this.value !== undefined && this.value !== null) {
            const selectedOption = this.options.find((option) => option.name === this.value);
            if (selectedOption) {
                this.selected = selectedOption;
                this.filteredOptions = [selectedOption, ...this.options.filter((option) => option.name !== this.value)];
                return;
            }
        }
        this.selected = this.options.find((option) => option.selected) || null;
        this.filteredOptions = this.options;
    },
};
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
