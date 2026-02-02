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
                return Array.isArray(this.modelValue) ? this.modelValue : [];
            },
            set(val) {
                this.$emit('update:modelValue', val);
            },
        },
        formTypeOptions() {
            return [
                { value: 'pre_registration', label: 'Pre-registration' },
                { value: 'pre_registration_biotech', label: 'Pre-registration + Quiz Bee' },
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
                    //id: crypto.randomUUID(),
                    form_type: null,
                    is_required: true,
                    max_slots: null,
                    config: {
                        attendance_type_required: false,
                        open_from: null,
                        open_to: null,
                    },
                },
            ];
        },
        removeRequirement(index) {
            const copy = this.requirements.slice();
            copy.splice(index, 1);
            this.requirements = copy;
        },
        handleTypeChange(index, value) {
            const copy = [...this.requirements];
            const prev = copy[index];
            copy[index] = {
                ...prev,
                form_type: value || null,
                config: {
                    attendance_type_required: prev.config?.attendance_type_required ?? false,
                    open_from: prev.config?.open_from ?? null,
                    open_to: prev.config?.open_to ?? null,
                },
            };

            // If this is a pre-registration/registration type, enable attendance config by default
            if (['pre_registration', 'pre_registration_biotech', 'registration'].includes(value)) {
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
        updateOpenFrom(index, value) {
            const copy = [...this.requirements];
            copy[index].config = {
                ...(copy[index].config || {}),
                open_from: value || null,
            };
            this.requirements = copy;
        },
        updateOpenTo(index, value) {
            const copy = [...this.requirements];
            copy[index].config = {
                ...(copy[index].config || {}),
                open_to: value || null,
            };
            this.requirements = copy;
        },
        availableFormTypeOptions(currentIndex) {
            const selectedTypes = this.requirements.map((r, idx) => (typeof currentIndex === 'number' && idx === currentIndex ? null : r.form_type)).filter(Boolean);
            return this.formTypeOptions.filter(opt => !selectedTypes.includes(opt.value));
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
                <div class="flex flex-col gap-1">
                    <div class="grid grid-cols-3 justify-center items-center gap-2">
                        <div class="flex flex-col gap-2">
                            <select
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm text-xs"
                                :value="req.form_type || ''"
                                @change="handleTypeChange(index, $event.target.value)"
                            >
                                <option value="" disabled>Select form type...</option>
                                <option
                                    v-for="opt in availableFormTypeOptions(index)"
                                    :key="opt.value"
                                    :value="opt.value"
                                >
                                    {{ opt.label }}
                                </option>
                            </select>
                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] text-gray-500">Max Slots (optional)</label>
                                <text-input
                                    type="number"
                                    min="0"
                                    placeholder="No limit"
                                    v-model="requirements[index].max_slots"
                                    class="text-xs"
                                />
                            </div>
                        </div>
                        <!-- Per-requirement open/close datetime config -->
                        <div class="flex flex-col items-center gap-2 text-[11px] mt-1">
                            <div class="flex items-center gap-1">
                                <span class="text-gray-500">Open:</span>
                                <input
                                    type="datetime-local"
                                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm text-[11px] px-1 py-0.5"
                                    :value="req.config?.open_from || ''"
                                    @change="updateOpenFrom(index, $event.target.value)"
                                />
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="text-gray-500">Close:</span>
                                <input
                                    type="datetime-local"
                                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm text-[11px] px-1 py-0.5"
                                    :value="req.config?.open_to || ''"
                                    @change="updateOpenTo(index, $event.target.value)"
                                />
                            </div>
                        </div>
                        <div class="flex items-end justify-center gap-1 flex-col">
                            <div class="flex items-center gap-1 text-xs">
                                <input type="checkbox" :checked="req.is_required" @change="toggleRequired(index)" />
                                <span>Required</span>
                            </div>
        
                            <div
                                v-if="['pre_registration', 'pre_registration_biotech', 'registration'].includes(req.form_type)"
                                class="flex items-center gap-2 text-xs mt-1"
                            >
                                <label class="flex items-center gap-1">
                                    <input
                                        type="checkbox"
                                        :checked="req.config?.attendance_type_required"
                                        @change="toggleAttendanceType(index)"
                                    />
                                    <span>Is this a Hybrid Event?</span>
                                </label>
                            </div>

                            <button
                                type="button"
                                class="text-xs text-red-500 hover:text-red-700"
                                @click="removeRequirement(index)"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button
            v-if="availableFormTypeOptions().length"
            type="button"
            class="mt-1 inline-flex items-center px-2 py-1 text-xs border border-dashed border-gray-400 rounded-md text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800"
            @click="addRequirement"
        >
            + Add a form
        </button>
    </div>
</template>
