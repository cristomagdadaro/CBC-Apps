<script>
import LocationMixin from "@/Modules/mixins/LocationMixin";
import FieldMixin from "@/Components/Forms/FieldMixin";

export default {
    name: "SelectCity",
    mixins: [LocationMixin, FieldMixin],
    props: {
        region: {
            type: String,
            default: "",
        },
        province: {
            type: String,
            default: "",
        },
    },
    data() {
        return {
            isOpen: false,
        };
    },
    computed: {
        selectedOption() {
            return this.cityOptions.find((opt) => opt.name === this.modelValue);
        },
        selectedLabel() {
            return this.selectedOption?.label || "Select city";
        },
        cityOptions() {
            return this.locationCities.map((city) => ({
                name: city.city ?? city,
                label: city.city ?? city,
            }));
        },
    },
    watch: {
        province(newProvince, oldProvince) {
            if (newProvince === oldProvince) {
                return;
            }

            this.isOpen = false;
            this.resetLocationCities();

            if (newProvince) {
                this.loadCities(newProvince, this.region);
            }
        },
    },
    methods: {
        selectOption(value) {
            this.$emit("update:modelValue", value);
            this.isOpen = false;
        },
        toggleDropdown() {
            if (!this.disabled && !this.locationLoading && this.cityOptions.length) {
                this.isOpen = !this.isOpen;
            }
        },
    },
    mounted() {
        if (this.province) {
            this.loadCities(this.province, this.region);
        }
    },
};
</script>

<template>
    <Field
        :id="id"
        :label="label"
        :error="error"
        :required="required"
        :hint="hint"
        :guide="guide"
        :clearable="clearable"
        :has-value="!!selectedOption"
        :disabled="disabled || locationLoading || !cityOptions.length"
        @clear="selectOption(null)">
        <template #label-icon>
            <LuMapPin class="h-3.5 w-3.5 text-indigo-500 dark:text-indigo-400" />
        </template>
        <template #default="{ inputId, isInvalid, isValid, guideId }">
            <div class="relative w-full">
                <button
                    :id="inputId"
                    type="button"
                    @click="toggleDropdown"
                    :disabled="disabled || locationLoading || !cityOptions.length"
                    :aria-invalid="isInvalid"
                    :aria-describedby="guideId"
                    :class="['flex w-full items-center justify-between gap-2 rounded-xl border-0 px-4 py-2.5 text-sm font-medium transition-all duration-200', 'bg-transparent text-slate-700 dark:text-slate-200', disabled || locationLoading || !cityOptions.length ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/50', isInvalid ? '' : 'focus:ring-0']">
                    <span class="truncate">{{ selectedLabel }}</span>
                    <LuChevronDown :class="['-me-0.5 ms-2 h-4 w-4 flex-shrink-0 text-slate-400 transition-transform', isOpen ? 'rotate-180' : '']" />
                </button>

                <!-- Backdrop -->
                <div
                    v-show="isOpen && cityOptions.length"
                    class="fixed inset-0 z-40"
                    @click.prevent="isOpen = false" />

                <!-- Dropdown Menu -->
                <transition-container type="fade">
                    <div
                        v-show="isOpen && cityOptions.length"
                        class="absolute z-50 mt-1.5 flex max-h-[30vh] w-full flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex-1 overflow-y-auto py-1">
                            <button
                                v-for="option in cityOptions"
                                :key="option.name"
                                type="button"
                                @click="selectOption(option.name)"
                                :class="['flex w-full items-center gap-2 px-4 py-2 text-left text-sm transition-colors', selectedOption?.name === option.name ? 'bg-indigo-50 font-medium text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800/50']">
                                {{ option.label }}
                            </button>
                        </div>
                    </div>
                </transition-container>
            </div>
        </template>
    </Field>
</template>
