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
        @clear="clearAll"
    >
        <template #label-icon>
            <LuList class="w-3.5 h-3.5 text-slate-400" />
        </template>
        <template #default="{ inputId, isInvalid, isValid, guideId }">
            <div class="relative w-full">
                <!-- Dropdown Trigger -->
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
                    <div class="flex gap-2 flex-wrap items-center flex-1">
                        <div v-if="selectedOptions.length === 0" class="text-slate-400 dark:text-slate-500 text-xs sm:text-sm">{{ placeholder }}</div>
                        <div v-for="option in selectedOptions" :key="option.value" class="flex gap-1 items-center bg-indigo-100 dark:bg-indigo-500/20 text-indigo-800 dark:text-indigo-300 px-2 py-1 rounded-md text-xs font-medium">
                            <span>{{ option.label }}</span>
                            <button
                                v-if="!disabled"
                                type="button"
                                @click.stop.prevent="removeOption(option.value)"
                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-200 font-bold"
                            >
                                <LuX class="w-3 h-3" />
                            </button>
                        </div>
                    </div>
                    <div class="flex gap-1.5 items-center flex-shrink-0 my-auto">
                        <LuChevronDown
                            v-if="!disabled"
                            :class="[
                                'w-4 h-4 text-slate-400 transition-transform duration-300',
                                open ? 'rotate-180' : ''
                            ]"
                        />
                    </div>
                </div>

                <div v-show="open" class="fixed inset-0 z-40" @click.prevent="open = false" />

                <transition-container type="fade">
                    <div
                        v-show="open"
                        class="z-50 absolute mt-1.5 w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-lg max-h-[40vh] overflow-hidden flex flex-col"
                    >
                        <div class="sticky top-0 bg-white dark:bg-slate-900 px-3 py-2 border-b border-slate-100 dark:border-slate-800 z-10">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search options..."
                                class="w-full border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 rounded-md px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:text-slate-200"
                                @keydown.esc="search = ''"
                            />
                        </div>
                        <div class="overflow-y-auto flex-1 py-1">
                            <div v-if="options.length === 0" class="text-center text-slate-500 text-xs py-4">
                                No options available
                            </div>
                            <div v-else-if="filteredOptions.length === 0" class="text-center text-slate-500 text-xs py-4">
                                No matching options
                            </div>
                            <label v-for="(option, index) in filteredOptions" :key="`${option.value}-${index}`" class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer transition-colors">
                                <input
                                    type="checkbox"
                                    :checked="isSelected(option.value)"
                                    @change="toggleOption(option.value)"
                                    class="rounded-sm border-slate-300 text-indigo-600 focus:ring-indigo-600 dark:border-slate-700 dark:bg-slate-900 cursor-pointer"
                                />
                                <span class="text-slate-700 dark:text-slate-300 text-xs sm:text-sm">{{ option.label }}</span>
                            </label>
                        </div>
                    </div>
                </transition-container>
            </div>
        </template>
    </Field>
</template>

<script>
import FieldMixin from '@/Components/Forms/FieldMixin';

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
                .filter(t => t.length > 0);

            if (searchTerms.length === 0) return this.options;

            return this.options.filter(opt => {
                const label = opt.label.toLowerCase();
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
