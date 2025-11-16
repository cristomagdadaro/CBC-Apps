<script>
import TextInput from '@/Components/TextInput.vue';
import InputError from "@/Components/InputError.vue";
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";

export default {
    name: 'RequirementsManager',
    components: {TransitionContainer, InputError, TextInput },
    props: {
        modelValue: {
            type: Array,
            default: () => [],
        },
        error: {
            type: String,
            default: null,
        },
    },
    emits: ['update:modelValue'],
    computed: {
        requirements: {
            get() {
                return this.modelValue;
            },
            set(val) {
                this.$emit('update:modelValue', val);
            },
        },
        formTypeOptions() {
            return [
                { value: 'pre_registration', label: 'Pre-registration' },
                { value: 'registration', label: 'Registration / Attendance' },
                { value: 'pre_test', label: 'Pre-test' },
                { value: 'post_test', label: 'Post-test' },
                { value: 'feedback', label: 'Feedback / Evaluation' },
            ];
        },
    },
    methods: {
        addRequirement() {
            this.requirements = [
                ...this.requirements,
                {
                    id: crypto.randomUUID(),
                    form_type: null,
                    is_required: true,
                    config: {
                        attendance_type_required: false,
                    },
                },
            ];
        },
        removeRequirement(index) {
            const copy = [...this.requirements];
            copy.splice(index, 1);
            this.requirements = copy;
        },
        handleTypeChange(index, value) {
            const copy = [...this.requirements];
            copy[index] = {
                ...copy[index],
                form_type: value,
            };

            // If this is a pre-registration/registration type, enable attendance config by default
            if (['pre_registration', 'registration'].includes(value)) {
                copy[index].config = {
                    ...copy[index].config,
                    attendance_type_required: true,
                };
            }

            this.requirements = copy;
        },
        toggleRequired(index) {
            const copy = [...this.requirements];
            copy[index] = {
                ...copy[index],
                is_required: !copy[index].is_required,
            };
            this.requirements = copy;
        },
        toggleAttendanceType(index) {
            const copy = [...this.requirements];
            const prev = !!copy[index].config?.attendance_type_required;
            copy[index].config = {
                ...(copy[index].config || {}),
                attendance_type_required: !prev,
            };
            this.requirements = copy;
        },
    },
};
</script>

<template>
    <div class="px-1 flex flex-col gap-2">
        <label class="font-bold uppercase flex items-center" title="Configure required forms for this event">
            Requirements:
            <transition-container type="slide-bottom">
                <InputError v-show="!!error" class="" :message="error" />
            </transition-container>
        </label>

        <div class="space-y-2">
            <div
                v-for="(req, index) in requirements"
                :key="req.id || index"
                class="flex flex-col gap-1 border border-gray-200 dark:border-gray-700 rounded-md p-2 bg-white dark:bg-gray-900"
            >
                <div class="flex items-center gap-2">
                    <select
                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm text-xs"
                        :value="req.form_type"
                        @change="handleTypeChange(index, $event.target.value)"
                    >
                        <option value="" disabled>Select form type...</option>
                        <option
                            v-for="opt in formTypeOptions"
                            :key="opt.value"
                            :value="opt.value"
                        >
                            {{ opt.label }}
                        </option>
                    </select>

                    <label class="flex items-center gap-1 text-xs">
                        <input type="checkbox" :checked="req.is_required" @change="toggleRequired(index)" />
                        <span>Required</span>
                    </label>

                    <button
                        type="button"
                        class="ml-auto text-xs text-red-500 hover:text-red-700"
                        @click="removeRequirement(index)"
                    >
                        Remove
                    </button>
                </div>

                <div
                    v-if="['pre_registration', 'registration'].includes(req.form_type)"
                    class="flex items-center gap-2 text-xs mt-1"
                >
                    <label class="flex items-center gap-1">
                        <input
                            type="checkbox"
                            :checked="req.config?.attendance_type_required"
                            @change="toggleAttendanceType(index)"
                        />
                        <span>Ask for Attendance Type (Online / In-person)</span>
                    </label>
                </div>
            </div>
        </div>

        <button
            type="button"
            class="mt-1 inline-flex items-center px-2 py-1 text-xs border border-dashed border-gray-400 rounded-md text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800"
            @click="addRequirement"
        >
            + Add requirement
        </button>
    </div>
</template>
