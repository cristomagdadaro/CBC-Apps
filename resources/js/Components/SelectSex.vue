<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import LoaderIcon from './Icons/LoaderIcon.vue';

export default {
  components: { LoaderIcon },
    name: 'SelectSex',
    mixins: [ApiMixin],
    props: {
        modelValue: {
            type: String,
            default: ''
        },
        disabled: Boolean,
        error: String
    },
    emits: ['update:modelValue'],
    data() {
        return {
            isOpen: false,
            sex_selections: [],
        };
    },
    computed: {
        selectedOption() {
            return this.sex_selections.find(opt => opt.value === this.modelValue) || null;
        },
        selectedLabel() {
            return this.selectedOption?.label || 'Select sex';
        }
    },
    methods: {
        async loadSex() {
            const response = await this.fetchGetApi('api.options.key', { routeParams: { key: 'sex' } });
            this.sex_selections = response?.value ?? [];
        },
        selectSex(value) {
            this.$emit('update:modelValue', value);
            this.isOpen = false;
        },
        toggleDropdown() {
            if (!this.disabled) {
                this.isOpen = !this.isOpen;
            }
        },
        close() {
            this.isOpen = false;
        }
    },
    mounted() {
        this.loadSex();
    }
};
</script>

<template>
    <div class="flex flex-col gap-1 w-fit">
        <div class="relative w-fit">
            <button
                type="button"
                @click="toggleDropdown"
                :disabled="disabled"
                :class="{
                    'border-red-500': error,
                    'opacity-50 cursor-not-allowed': disabled
                }"
                class="inline-flex items-center justify-between px-3 py-3 border border-gray-500 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white w-full dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150"
            >
                <div class="flex items-center gap-2 whitespace-nowrap">
                    <svg v-if="selectedOption" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" :class="selectedOption.color" viewBox="0 0 16 16">
                        <g v-if="selectedOption.icon === 'gender-male'">
                            <path fill-rule="evenodd" d="M9.5 2a.5.5 0 0 1 0-1h5a.5.5 0 0 1 .5.5v5a.5.5 0 0 1-1 0V2.707L9.871 6.836a5 5 0 1 1-.707-.707L13.293 2zM6 6a4 4 0 1 0 0 8 4 4 0 0 0 0-8"/>
                        </g>
                        <g v-else-if="selectedOption.icon === 'gender-female'">
                            <path fill-rule="evenodd" d="M8 1a4 4 0 1 0 0 8 4 4 0 0 0 0-8M3 5a5 5 0 1 1 5.5 4.975V12h2a.5.5 0 0 1 0 1h-2v2.5a.5.5 0 0 1-1 0V13h-2a.5.5 0 0 1 0-1h2V9.975A5 5 0 0 1 3 5"/>
                        </g>
                        <g v-else>
                            <path fill-rule="evenodd" d="m8 2.42-.717-.737c-1.13-1.161-3.243-.777-4.01.72-.35.685-.451 1.707.236 3.062C4.16 6.753 5.52 8.32 8 10.042c2.479-1.723 3.839-3.29 4.491-4.577.687-1.355.587-2.377.236-3.061-.767-1.498-2.88-1.882-4.01-.721zm-.49 8.5c-10.78-7.44-3-13.155.359-10.063q.068.062.132.129.065-.067.132-.129c3.36-3.092 11.137 2.624.357 10.063l.235.468a.25.25 0 1 1-.448.224l-.008-.017c.008.11.02.202.037.29.054.27.161.488.419 1.003.288.578.235 1.15.076 1.629-.157.469-.422.867-.588 1.115l-.004.007a.25.25 0 1 1-.416-.278c.168-.252.4-.6.533-1.003.133-.396.163-.824-.049-1.246l-.013-.028c-.24-.48-.38-.758-.448-1.102a3 3 0 0 1-.052-.45l-.04.08a.25.25 0 1 1-.447-.224l.235-.468ZM6.013 2.06c-.649-.18-1.483.083-1.85.798-.131.258-.245.689-.08 1.335.063.244.414.198.487-.043.21-.697.627-1.447 1.359-1.692.217-.073.304-.337.084-.398"/>
                        </g>
                    </svg>
                    <span>{{ selectedLabel }}</span>
                </div>
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
                    v-show="isOpen"
                    class="absolute right-0 z-50 w-fit mt-2 bg-white dark:bg-gray-800 rounded-md shadow-lg ring-1 ring-black ring-opacity-5"
                >
                    <div class="py-1 w-fit">
                        <loader-icon v-if="processing" class="mx-auto py-2" />
                        <button
                            v-for="option in sex_selections"
                            :key="option.value"
                            type="button"
                            @click="selectSex(option.value)"
                            :class="{ 'bg-gray-100 dark:bg-gray-700': selectedOption?.value === option.value }"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 transition"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" :class="option.color" viewBox="0 0 16 16">
                                <g v-if="option.icon === 'gender-male'">
                                    <path fill-rule="evenodd" d="M9.5 2a.5.5 0 0 1 0-1h5a.5.5 0 0 1 .5.5v5a.5.5 0 0 1-1 0V2.707L9.871 6.836a5 5 0 1 1-.707-.707L13.293 2zM6 6a4 4 0 1 0 0 8 4 4 0 0 0 0-8"/>
                                </g>
                                <g v-else-if="option.icon === 'gender-female'">
                                    <path fill-rule="evenodd" d="M8 1a4 4 0 1 0 0 8 4 4 0 0 0 0-8M3 5a5 5 0 1 1 5.5 4.975V12h2a.5.5 0 0 1 0 1h-2v2.5a.5.5 0 0 1-1 0V13h-2a.5.5 0 0 1 0-1h2V9.975A5 5 0 0 1 3 5"/>
                                </g>
                                <g v-else>
                                    <path fill-rule="evenodd" d="m8 2.42-.717-.737c-1.13-1.161-3.243-.777-4.01.72-.35.685-.451 1.707.236 3.062C4.16 6.753 5.52 8.32 8 10.042c2.479-1.723 3.839-3.29 4.491-4.577.687-1.355.587-2.377.236-3.061-.767-1.498-2.88-1.882-4.01-.721zm-.49 8.5c-10.78-7.44-3-13.155.359-10.063q.068.062.132.129.065-.067.132-.129c3.36-3.092 11.137 2.624.357 10.063l.235.468a.25.25 0 1 1-.448.224l-.008-.017c.008.11.02.202.037.29.054.27.161.488.419 1.003.288.578.235 1.15.076 1.629-.157.469-.422.867-.588 1.115l-.004.007a.25.25 0 1 1-.416-.278c.168-.252.4-.6.533-1.003.133-.396.163-.824-.049-1.246l-.013-.028c-.24-.48-.38-.758-.448-1.102a3 3 0 0 1-.052-.45l-.04.08a.25.25 0 1 1-.447-.224l.235-.468ZM6.013 2.06c-.649-.18-1.483.083-1.85.798-.131.258-.245.689-.08 1.335.063.244.414.198.487-.043.21-.697.627-1.447 1.359-1.692.217-.073.304-.337.084-.398"/>
                                </g>
                            </svg>
                            <span class="whitespace-nowrap">{{ option.label }}</span>
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
