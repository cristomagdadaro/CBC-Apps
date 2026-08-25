<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import LoaderIcon from "./Icons/LoaderIcon.vue";
import FieldMixin from "@/Components/Forms/FieldMixin";

export default {
    components: { LoaderIcon },
    name: "SelectSex",
    mixins: [ApiMixin, FieldMixin],
    data() {
        return {
            isOpen: false,
            sex_selections: [],
        };
    },
    computed: {
        selectedOption() {
            return this.sex_selections.find((opt) => opt.value === this.modelValue) || null;
        },
        selectedLabel() {
            return this.selectedOption?.label || "Select sex";
        },
    },
    methods: {
        async loadSex() {
            const response = await this.fetchGetApi("api.options.key", {
                routeParams: { key: "sex" },
            });
            this.sex_selections = response?.value ?? [];
        },
        selectSex(value) {
            this.$emit("update:modelValue", value);
            this.isOpen = false;
        },
        toggleDropdown() {
            if (!this.disabled) {
                this.isOpen = !this.isOpen;
            }
        },
    },
    mounted() {
        this.loadSex();
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
        :disabled="disabled"
        @clear="selectSex(null)">
        <template #label-icon>
            <LuUsers class="w-3.5 h-3.5 text-indigo-500 dark:text-indigo-400" />
        </template>
        <template #default="{ inputId, isInvalid, isValid, guideId }">
            <div class="relative w-full">
                <button
                    :id="inputId"
                    type="button"
                    @click="toggleDropdown"
                    :disabled="disabled"
                    :aria-invalid="isInvalid"
                    :aria-describedby="guideId"
                    :class="['w-full flex gap-2 justify-between items-center rounded-xl px-4 py-2.5 transition-all duration-200 text-sm font-medium border-0', 'bg-transparent text-slate-700 dark:text-slate-200', disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/50', isInvalid ? '' : 'focus:ring-0']">
                    <div class="flex items-center gap-2 whitespace-nowrap">
                        <svg
                            v-if="selectedOption"
                            xmlns="http://www.w3.org/2000/svg"
                            width="16"
                            height="16"
                            fill="currentColor"
                            :class="selectedOption.color"
                            viewBox="0 0 16 16">
                            <g v-if="selectedOption.icon === 'gender-male'">
                                <path
                                    fill-rule="evenodd"
                                    d="M9.5 2a.5.5 0 0 1 0-1h5a.5.5 0 0 1 .5.5v5a.5.5 0 0 1-1 0V2.707L9.871 6.836a5 5 0 1 1-.707-.707L13.293 2zM6 6a4 4 0 1 0 0 8 4 4 0 0 0 0-8" />
                            </g>
                            <g v-else-if="selectedOption.icon === 'gender-female'">
                                <path
                                    fill-rule="evenodd"
                                    d="M8 1a4 4 0 1 0 0 8 4 4 0 0 0 0-8M3 5a5 5 0 1 1 5.5 4.975V12h2a.5.5 0 0 1 0 1h-2v2.5a.5.5 0 0 1-1 0V13h-2a.5.5 0 0 1 0-1h2V9.975A5 5 0 0 1 3 5" />
                            </g>
                            <g v-else>
                                <path
                                    fill-rule="evenodd"
                                    d="m8 2.42-.717-.737c-1.13-1.161-3.243-.777-4.01.72-.35.685-.451 1.707.236 3.062C4.16 6.753 5.52 8.32 8 10.042c2.479-1.723 3.839-3.29 4.491-4.577.687-1.355.587-2.377.236-3.061-.767-1.498-2.88-1.882-4.01-.721zm-.49 8.5c-10.78-7.44-3-13.155.359-10.063q.068.062.132.129.065-.067.132-.129c3.36-3.092 11.137 2.624.357 10.063l.235.468a.25.25 0 1 1-.448.224l-.008-.017c.008.11.02.202.037.29.054.27.161.488.419 1.003.288.578.235 1.15.076 1.629-.157.469-.422.867-.588 1.115l-.004.007a.25.25 0 1 1-.416-.278c.168-.252.4-.6.533-1.003.133-.396.163-.824-.049-1.246l-.013-.028c-.24-.48-.38-.758-.448-1.102a3 3 0 0 1-.052-.45l-.04.08a.25.25 0 1 1-.447-.224l.235-.468ZM6.013 2.06c-.649-.18-1.483.083-1.85.798-.131.258-.245.689-.08 1.335.063.244.414.198.487-.043.21-.697.627-1.447 1.359-1.692.217-.073.304-.337.084-.398" />
                            </g>
                        </svg>
                        <span>{{ selectedLabel }}</span>
                    </div>
                    <LuChevronDown :class="['ms-2 -me-0.5 h-4 w-4 transition-transform flex-shrink-0 text-slate-400', isOpen ? 'rotate-180' : '']" />
                </button>

                <!-- Backdrop -->
                <div
                    v-show="isOpen"
                    class="fixed inset-0 z-40"
                    @click.prevent="isOpen = false" />

                <!-- Dropdown Menu -->
                <transition-container type="fade">
                    <div
                        v-show="isOpen"
                        class="z-50 absolute w-full mt-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-lg max-h-[30vh] overflow-hidden flex flex-col">
                        <div class="overflow-y-auto flex-1 py-1">
                            <loader-icon
                                v-if="processing"
                                class="mx-auto py-2" />
                            <button
                                v-for="option in sex_selections"
                                :key="option.value"
                                type="button"
                                @click="selectSex(option.value)"
                                :class="['w-full text-left px-4 py-2 text-sm flex items-center gap-2 transition-colors', selectedOption?.value === option.value ? 'bg-indigo-50 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-medium' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50']">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="16"
                                    height="16"
                                    fill="currentColor"
                                    :class="option.color"
                                    viewBox="0 0 16 16">
                                    <g v-if="option.icon === 'gender-male'">
                                        <path
                                            fill-rule="evenodd"
                                            d="M9.5 2a.5.5 0 0 1 0-1h5a.5.5 0 0 1 .5.5v5a.5.5 0 0 1-1 0V2.707L9.871 6.836a5 5 0 1 1-.707-.707L13.293 2zM6 6a4 4 0 1 0 0 8 4 4 0 0 0 0-8" />
                                    </g>
                                    <g v-else-if="option.icon === 'gender-female'">
                                        <path
                                            fill-rule="evenodd"
                                            d="M8 1a4 4 0 1 0 0 8 4 4 0 0 0 0-8M3 5a5 5 0 1 1 5.5 4.975V12h2a.5.5 0 0 1 0 1h-2v2.5a.5.5 0 0 1-1 0V13h-2a.5.5 0 0 1 0-1h2V9.975A5 5 0 0 1 3 5" />
                                    </g>
                                    <g v-else>
                                        <path
                                            fill-rule="evenodd"
                                            d="m8 2.42-.717-.737c-1.13-1.161-3.243-.777-4.01.72-.35.685-.451 1.707.236 3.062C4.16 6.753 5.52 8.32 8 10.042c2.479-1.723 3.839-3.29 4.491-4.577.687-1.355.587-2.377.236-3.061-.767-1.498-2.88-1.882-4.01-.721zm-.49 8.5c-10.78-7.44-3-13.155.359-10.063q.068.062.132.129.065-.067.132-.129c3.36-3.092 11.137 2.624.357 10.063l.235.468a.25.25 0 1 1-.448.224l-.008-.017c.008.11.02.202.037.29.054.27.161.488.419 1.003.288.578.235 1.15.076 1.629-.157.469-.422.867-.588 1.115l-.004.007a.25.25 0 1 1-.416-.278c.168-.252.4-.6.533-1.003.133-.396.163-.824-.049-1.246l-.013-.028c-.24-.48-.38-.758-.448-1.102a3 3 0 0 1-.052-.45l-.04.08a.25.25 0 1 1-.447-.224l.235-.468ZM6.013 2.06c-.649-.18-1.483.083-1.85.798-.131.258-.245.689-.08 1.335.063.244.414.198.487-.043.21-.697.627-1.447 1.359-1.692.217-.073.304-.337.084-.398" />
                                    </g>
                                </svg>
                                <span class="whitespace-nowrap">{{ option.label }}</span>
                            </button>
                        </div>
                    </div>
                </transition-container>
            </div>
        </template>
    </Field>
</template>
