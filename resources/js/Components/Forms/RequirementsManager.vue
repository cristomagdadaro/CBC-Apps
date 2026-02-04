<script>
import TextInput from '@/Components/TextInput.vue';
import InputError from "@/Components/InputError.vue";
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";
import CaretDown from "@/Components/Icons/CaretDown.vue";

export default {
    name: 'RequirementsManager',
    components: {TransitionContainer, InputError, TextInput, CaretDown },
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
        sortedRequirements() {
            return [...this.requirements].sort((a, b) => {
                const aOrder = a.step_order ?? Number.MAX_SAFE_INTEGER;
                const bOrder = b.step_order ?? Number.MAX_SAFE_INTEGER;
                return aOrder - bOrder;
            });
        },
        formTypeOptions() {
            return [
                { value: 'preregistration', label: 'Pre-registration' },
                { value: 'preregistration_biotech', label: 'Pre-registration + Quiz Bee' },
                { value: 'registration', label: 'Registration / Attendance' },
                { value: 'pretest', label: 'Pre-test' },
                { value: 'posttest', label: 'Post-test' },
                { value: 'feedback', label: 'Feedback / Evaluation' },
            ];
        },
    },
    methods: {
        normalizeRequirements() {
            if (!this.requirements.length) {
                return;
            }

            const copy = [...this.requirements];
            copy.sort((a, b) => {
                const aOrder = a.step_order ?? Number.MAX_SAFE_INTEGER;
                const bOrder = b.step_order ?? Number.MAX_SAFE_INTEGER;
                return aOrder - bOrder;
            });

            copy.forEach((req, index) => {
                req.step_order = index + 1;
            });

            this.requirements = copy;
        },
        getNextStepOrder() {
            const orders = this.requirements
                .map((req) => Number(req.step_order))
                .filter((value) => Number.isFinite(value) && value > 0);

            const maxOrder = orders.length ? Math.max(...orders) : 0;
            return maxOrder + 1;
        },
        addRequirement() {
            const nextStepOrder = this.getNextStepOrder();
            this.requirements = [
                ...this.requirements,
                {
                    form_type: null,
                    step_type: null,
                    step_order: nextStepOrder,
                    is_required: true,
                    is_enabled: true,
                    max_slots: null,
                    open_from: null,
                    open_to: null,
                    visibility_rules: {},
                    completion_rules: {},
                },
            ];
            this.normalizeRequirements();
        },
        removeRequirement(index) {
            const copy = this.requirements.slice();
            copy.splice(index, 1);
            this.requirements = copy;
            this.normalizeRequirements();
        },
        handleTypeChange(index, value) {
            const copy = [...this.requirements];
            const prev = copy[index];
            copy[index] = {
                ...prev,
                form_type: value || null,
                step_type: prev.step_type || value || null,
                step_order: prev.step_order || index + 1,
                visibility_rules: prev.visibility_rules || {},
                completion_rules: prev.completion_rules || {},
            };
            
            this.requirements = copy;
            this.normalizeRequirements();
        },
        toggleRequired(index) {
            const copy = [...this.requirements];
            copy[index] = {
                ...copy[index],
                is_required: !copy[index].is_required,
            };
            this.requirements = copy;
        },
        toggleEnabled(index) {
            const copy = [...this.requirements];
            copy[index] = {
                ...copy[index],
                is_enabled: !copy[index].is_enabled,
            };
            this.requirements = copy;
        },
        updateMaxSlots(index, value) {
            const copy = [...this.requirements];
            copy[index].max_slots = value ? parseInt(value) : null;
            this.requirements = copy;
        },
        updateOpenFrom(index, value) {
            const copy = [...this.requirements];
            copy[index].open_from = value || null;
            this.requirements = copy;
        },
        updateOpenTo(index, value) {
            const copy = [...this.requirements];
            copy[index].open_to = value || null;
            this.requirements = copy;
        },
        moveRequirement(index, direction) {
            const copy = [...this.requirements];
            const target = index + direction;

            if (target < 0 || target >= copy.length) {
                return;
            }

            const temp = copy[index];
            copy[index] = copy[target];
            copy[target] = temp;
            this.requirements = copy;
            this.normalizeRequirements();
        },
        availableFormTypeOptions(currentIndex) {
            const selectedTypes = this.requirements.map((r, idx) => (typeof currentIndex === 'number' && idx === currentIndex ? null : r.form_type)).filter(Boolean);
            return this.formTypeOptions.filter(opt => !selectedTypes.includes(opt.value));
        },
    },
    mounted() {
        this.normalizeRequirements();
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
                v-for="(req, index) in sortedRequirements"
                :key="req.id || index"
                class="relative flex flex-col gap-1 border border-gray-200 dark:border-gray-700 rounded-md p-2 bg-white dark:bg-gray-900"
            >
                <div class="flex w-full justify-between">
                    <span class="text-xs">STEP {{ req.step_order ?? index + 1 }}</span>
                    <div class="flex gap-1">
                        <div class="flex items-center gap-1 text-xs border p-0.5">
                            <input type="checkbox" :checked="req.is_enabled !== false" @change="toggleEnabled(index)" class="rounded-full" />
                            <span>Enabled</span>
                        </div>
                        <div class="flex items-center gap-1 text-xs">
                            <button type="button" class="px-2 py-1 border rounded flex items-center gap-1" @click="moveRequirement(index, -1)">
                                <caret-down class="w-3 h-3 transform -rotate-180" />
                                Up
                            </button>
                            <button type="button" class="px-2 py-1 border rounded flex items-center gap-1" @click="moveRequirement(index, 1)">
                                <caret-down class="w-3 h-3 transform" />
                                Down
                            </button>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <div class="grid grid-cols-3 justify-center items-center gap-2">
                        <div class="grid grid-rows-2 gap-2">
                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] text-gray-500">Form Type</label>
                                <select
                                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm text-xs py-0.5 px-2"
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
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] text-gray-500">Max Slots (optional)</label>
                                <input
                                    type="number"
                                    min="0"
                                    placeholder="No limit"
                                    :value="req.max_slots || ''"
                                    @change="updateMaxSlots(index, $event.target.value)"
                                    class="text-xs p-0 px-2 rounded-md" />
                            </div>
                        </div>
                        <!-- Per-requirement open/close datetime config -->
                        <div class="grid grid-rows-2 items-center gap-2 text-[11px] mt-1 w-full">
                            <div class="flex flex-col items-start gap-1">
                                <span class="text-gray-500">Open:</span>
                                <input
                                    type="datetime-local"
                                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm text-[11px] px-1 py-0.5"
                                    :value="req.open_from || ''"
                                    @change="updateOpenFrom(index, $event.target.value)"
                                />
                            </div>
                            <div class="flex flex-col items-start gap-1">
                                <span class="text-gray-500">Close:</span>
                                <input
                                    type="datetime-local"
                                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm text-[11px] px-1 py-0.5"
                                    :value="req.open_to || ''"
                                    @change="updateOpenTo(index, $event.target.value)"
                                />
                            </div>
                        </div>
                        <div class="flex items-end justify-center gap-1 flex-col">
                            <div class="flex items-center gap-1 text-xs">
                                <input type="checkbox" :checked="req.is_required" @change="toggleRequired(index)" class="rounded-full" />
                                <span>Required</span>
                            </div>
                            <button
                                type="button"
                                class="text-xs text-red-500 hover:text-red-700 mt-1"
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
