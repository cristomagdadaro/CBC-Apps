<template>
    <Field
        :id="id"
        :label="label"
        :error="error"
        :required="required"
        :hint="hint"
        :guide="guide"
        :clearable="showClear"
        :has-value="selectedValues.length > 0"
        :disabled="disabled"
        @clear="clearAll">
        <template #label-icon>
            <LuList class="h-3.5 w-3.5 text-slate-400" />
        </template>
        <template #default="{ inputId, isInvalid, isValid, guideId }">
            <div class="relative w-full">
                <!-- Dropdown Trigger -->
                <div
                    :id="inputId"
                    :class="['flex w-full items-center justify-between gap-2 rounded-xl px-3 py-2.5 transition-all duration-200', 'bg-transparent', disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer', isInvalid ? '' : 'focus-within:ring-0']"
                    :aria-invalid="isInvalid"
                    :aria-describedby="guideId"
                    @click.prevent="toggle">
                    <div class="flex flex-1 flex-wrap items-center gap-2">
                        <div
                            v-if="selectedOptions.length === 0"
                            class="text-xs text-slate-400 sm:text-sm dark:text-slate-500">
                            {{ placeholder }}
                        </div>
                        <div
                            v-for="option in selectedOptions"
                            :key="option.value"
                            class="flex items-center gap-1 rounded-md bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-800 dark:bg-indigo-500/20 dark:text-indigo-300">
                            <span>{{ option.label }}</span>
                            <button
                                v-if="!disabled"
                                type="button"
                                @click.stop.prevent="removeOption(option.value)"
                                class="font-bold text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200">
                                <LuX class="h-3 w-3" />
                            </button>
                        </div>
                    </div>
                    <div class="my-auto flex flex-shrink-0 items-center gap-1.5">
                        <LuChevronDown
                            v-if="!disabled"
                            :class="['h-4 w-4 text-slate-400 transition-transform duration-300', open ? 'rotate-180' : '']" />
                    </div>
                </div>

                <div
                    v-show="open"
                    class="fixed inset-0 z-40"
                    @click.prevent="open = false" />

                <transition-container type="fade">
                    <div
                        v-show="open"
                        class="absolute z-50 mt-1.5 flex max-h-[40vh] w-full flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg dark:border-slate-800 dark:bg-slate-900">
                        <div class="sticky top-0 z-10 border-b border-slate-100 bg-white px-3 py-2 dark:border-slate-800 dark:bg-slate-900">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search options..."
                                class="w-full rounded-md border-slate-200 bg-slate-50 px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                @keydown.esc="search = ''" />
                        </div>
                        <div class="flex-1 overflow-y-auto py-1">
                            <div
                                v-if="options.length === 0"
                                class="py-4 text-center text-xs text-slate-500">
                                No options available
                            </div>
                            <div
                                v-else-if="filteredOptions.length === 0"
                                class="py-4 text-center text-xs text-slate-500">
                                No matching options
                            </div>
                            <label
                                v-for="(option, index) in filteredOptions"
                                :key="`${option.value}-${index}`"
                                class="flex cursor-pointer items-center gap-3 px-4 py-2.5 transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <input
                                    type="checkbox"
                                    :checked="isSelected(option.value)"
                                    @change="toggleOption(option.value)"
                                    class="cursor-pointer rounded-sm border-slate-300 text-indigo-600 focus:ring-indigo-600 dark:border-slate-700 dark:bg-slate-900" />
                                <span class="text-xs text-slate-700 sm:text-sm dark:text-slate-300">
                                    {{ option.label }}
                                </span>
                            </label>
                        </div>
                    </div>
                </transition-container>
            </div>
        </template>
    </Field>
</template>

<script>
import FieldMixin from "@/Components/Forms/FieldMixin";

export default {
    name: "MultiSelectDropdown",
    mixins: [FieldMixin],
    props: {
        modelValue: {
            type: Array,
            default: () => [],
        },
        options: {
            type: Array,
            default: () => [],
        },
        showClear: {
            type: Boolean,
            default: true,
        },
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

            const searchTerms = this.search
                .toLowerCase()
                .trim()
                .split(/\s+/)
                .filter((t) => t.length > 0);

            if (searchTerms.length === 0) return this.options;

            return this.options.filter((opt) => {
                const label = opt.label.toLowerCase();
                return searchTerms.every((term) => label.includes(term));
            });
        },
        selectedOptions() {
            return this.options.filter((opt) => this.selectedValues.includes(opt.value));
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
                this.$emit("update:modelValue", [...this.selectedValues]);
            }
        },
        removeOption(value) {
            const index = this.selectedValues.indexOf(value);
            if (index > -1) {
                this.selectedValues.splice(index, 1);
                this.$emit("update:modelValue", [...this.selectedValues]);
            }
        },
        clearAll() {
            this.selectedValues = [];
            this.$emit("update:modelValue", []);
        },
    },
    mounted() {
        this.selectedValues = Array.isArray(this.modelValue) ? [...this.modelValue] : [];
    },
};
</script>
