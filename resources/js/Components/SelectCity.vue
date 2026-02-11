<script>
import LocationMixin from '@/Modules/mixins/LocationMixin';

export default {
    name: 'SelectCity',
    mixins: [LocationMixin],
    props: {
        modelValue: {
            type: String,
            default: ''
        },
        region: {
            type: String,
            default: ''
        },
        province: {
            type: String,
            default: ''
        },
        disabled: Boolean,
        error: String
    },
    emits: ['update:modelValue'],
    data() {
        return {
            isOpen: false
        };
    },
    computed: {
        selectedOption() {
            return this.cityOptions.find(opt => opt.name === this.modelValue);
        },
        selectedLabel() {
            return this.selectedOption?.label || 'Select city';
        },
        cityOptions() {
            return this.locationCities.map(city => ({ name: city.city ?? city, label: city.city ?? city }));
        }
    },
    watch: {
        province(newProvince) {
            if (newProvince) {
                this.loadCities(newProvince, this.region);
            } else {
                this.locationCities = [];
            }
        }
    },
    methods: {
        selectOption(value) {
            this.$emit('update:modelValue', value);
            this.isOpen = false;
        },
        toggleDropdown() {
            if (!this.disabled && !this.locationLoading && this.cityOptions.length) {
                this.isOpen = !this.isOpen;
            }
        }
    },
    mounted() {
        if (this.province) {
            this.loadCities(this.province, this.region);
        }
    }
};
</script>

<template>
    <div class="flex flex-col gap-1">
        <div class="relative">
            <button
                type="button"
                @click="toggleDropdown"
                :disabled="disabled || locationLoading || !cityOptions.length"
                :class="{
                    'border-red-500': error,
                    'opacity-50 cursor-not-allowed': disabled || locationLoading || !cityOptions.length
                }"
                class="inline-flex items-center justify-between px-3 py-3 border shadow-sm text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white w-full dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150"
            >
                <span>{{ selectedLabel }}</span>
                <svg
                    class="ms-2 -me-0.5 h-4 w-4 transition-transform flex-shrink-0"
                    :class="{ 'rotate-180': isOpen }"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                </svg>
            </button>

            <!-- Dropdown Menu -->
            <transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
            >
                <div
                    v-show="isOpen && cityOptions.length"
                    class="absolute left-0 z-50 w-full mt-2 bg-white dark:bg-gray-800 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 max-h-48 overflow-y-auto"
                >
                    <div class="py-1">
                        <button
                            v-for="option in cityOptions"
                            :key="option.name"
                            type="button"
                            @click="selectOption(option.name)"
                            :class="{ 'bg-gray-100 dark:bg-gray-700': selectedOption?.name === option.name }"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                        >
                            {{ option.label }}
                        </button>
                    </div>
                </div>
            </transition>
        </div>

        <!-- Error Message -->
        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <p v-if="error" class="text-sm text-red-500">{{ error }}</p>
        </transition>
    </div>
</template>
