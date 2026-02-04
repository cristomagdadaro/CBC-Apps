<script>
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import TransitionContainer from '@/Components/Transitions/TransitionContrainer.vue'
import CaretDown from '@/Components/Icons/CaretDown.vue'
import { toRaw } from 'vue'
import AddIcon from '@/Components/Icons/AddIcon.vue'

export default {
    name: 'RequirementsManager',
    components: { TransitionContainer, InputError, TextInput, CaretDown, AddIcon },

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

    data() {
        return {
            localErrors: {},
            successMessage: null,
            successTimeout: null,
        }
    },

    computed: {
        requirements() {
            return Array.isArray(this.modelValue) ? this.modelValue : []
        },

        sortedRequirements() {
            return [...this.requirements].sort((a, b) => {
                const aOrder = a.step_order ?? Number.MAX_SAFE_INTEGER
                const bOrder = b.step_order ?? Number.MAX_SAFE_INTEGER
                return aOrder - bOrder
            })
        },

        formTypeOptions() {
            return [
                { value: 'preregistration', label: 'Pre-registration' },
                { value: 'preregistration_biotech', label: 'Pre-registration + Quiz Bee' },
                { value: 'preregistration_quizbee', label: 'Pre-registration Quiz Bee' },
                { value: 'registration', label: 'Registration / Attendance' },
                { value: 'pretest', label: 'Pre-test' },
                { value: 'posttest', label: 'Post-test' },
                { value: 'feedback', label: 'Feedback / Evaluation' },
            ]
        },
        limitFieldOptions() {
            return [
                { value: 'province_address', label: 'Province' },
                { value: 'city_address', label: 'City' },
                { value: 'organization', label: 'Organization' },
                { value: 'designation', label: 'Designation/Position' },
                { value: 'attendance_type', label: 'Attendance Type' },
                { value: 'participant_1_gradelevel', label: 'Participant 1 Grade Level' },
                { value: 'participant_2_gradelevel', label: 'Participant 2 Grade Level' },
                { value: 'participant_1_sex', label: 'Participant 1 Sex' },
                { value: 'participant_2_sex', label: 'Participant 2 Sex' },
                { value: 'team_name', label: 'Team Name' },
            ]
        },
    },

    methods: {
        cloneRequirements() {
            return this.requirements.map(r => {
                const raw = toRaw(r)
                const config = raw?.config ?? {}
                return {
                    ...raw,
                    config: {
                        ...config,
                        limits: Array.isArray(config?.limits) ? config.limits.map(limit => ({ ...limit })) : [],
                    },
                }
            })
        },

        emitUpdate(list) {
            this.$emit('update:modelValue', list)
        },

        setError(field, message) {
            this.localErrors[field] = message
        },

        clearError(field) {
            delete this.localErrors[field]
        },

        showSuccess(message) {
            this.successMessage = message
            clearTimeout(this.successTimeout)
            this.successTimeout = setTimeout(() => {
                this.successMessage = null
            }, 3000)
        },
        validateRequirement(req, index) {
            const errors = []

            if (!req.form_type) {
                errors.push(`Step ${index + 1}: Form type is required`)
            }

            if (req.step_order != null && (!Number.isInteger(req.step_order) || req.step_order < 1)) {
                errors.push(`Step ${index + 1}: Step order must be a positive integer`)
            }

            if (req.max_slots != null && (isNaN(req.max_slots) || req.max_slots < 0)) {
                errors.push(`Step ${index + 1}: Max slots must be a non-negative integer`)
            }

            if (req.open_from && req.open_to) {
                const from = new Date(req.open_from)
                const to = new Date(req.open_to)
                if (from >= to) {
                    errors.push(`Step ${index + 1}: Open time must be before close time`)
                }
            }

            return errors
        },
        normalize(list) {
            const copy = [...list]

            copy.sort((a, b) => {
                const aOrder = a.step_order ?? Number.MAX_SAFE_INTEGER
                const bOrder = b.step_order ?? Number.MAX_SAFE_INTEGER
                return aOrder - bOrder
            })

            copy.forEach((req, i) => {
                req.step_order = i + 1
            })

            return copy
        },
        addRequirement() {
            const copy = this.cloneRequirements()

            copy.push({
                form_type: null,
                step_type: null,
                step_order: copy.length + 1,
                is_required: true,
                is_enabled: true,
                max_slots: null,
                open_from: null,
                open_to: null,
                config: {
                    limits: [],
                },
                visibility_rules: {},
                completion_rules: {},
            })

            this.emitUpdate(this.normalize(copy))
            this.showSuccess('New form added')
        },

        removeRequirement(index) {
            const sorted = this.sortedRequirements[index]
            const actualIndex = this.requirements.indexOf(sorted)
            if (actualIndex === -1) return

            const copy = this.cloneRequirements()
            copy.splice(actualIndex, 1)

            this.emitUpdate(this.normalize(copy))
            this.showSuccess('Form removed')
        },

        handleTypeChange(index, value) {
            if (!value) {
                this.setError(`req_${index}_type`, 'Form type is required')
                return
            }

            const copy = this.cloneRequirements()

            if (copy.some((r, i) => i !== index && r.form_type === value)) {
                this.setError(`req_${index}_type`, `Form type "${value}" already used`)
                return
            }

            copy[index] = {
                ...copy[index],
                form_type: value,
                step_type: copy[index].step_type ?? value,
                id: null,
                config: copy[index].config ?? { limits: [] },
            }

            this.clearError(`req_${index}_type`)
            this.emitUpdate(this.normalize(copy))
            this.showSuccess('Form type updated')
        },

        toggleRequired(index) {
            const copy = this.cloneRequirements()
            copy[index].is_required = !copy[index].is_required
            this.emitUpdate(copy)
            this.showSuccess(copy[index].is_required ? 'Form marked as required' : 'Form marked as optional')
        },

        toggleEnabled(index) {
            const sorted = this.sortedRequirements[index]
            const actualIndex = this.requirements.indexOf(sorted)
            if (actualIndex === -1) return

            const copy = this.cloneRequirements()
            copy[actualIndex].is_enabled = !copy[actualIndex].is_enabled
            this.emitUpdate(copy)
            this.showSuccess(copy[actualIndex].is_enabled ? 'Form enabled' : 'Form disabled')
        },

        updateMaxSlots(index, value) {
            const num = value ? parseInt(value) : null
            if (num != null && num < 0) return

            const copy = this.cloneRequirements()
            copy[index].max_slots = num
            this.emitUpdate(copy)
            this.showSuccess('Max slots updated')
        },

        updateOpenFrom(index, value) {
            const copy = this.cloneRequirements()
            copy[index].open_from = value || null
            this.emitUpdate(copy)
            this.showSuccess('Open from date updated')
        },

        updateOpenTo(index, value) {
            const copy = this.cloneRequirements()
            copy[index].open_to = value || null
            this.emitUpdate(copy)
            this.showSuccess('Open to date updated')
        },

        addLimit(index) {
            const copy = this.cloneRequirements()
            if (!copy[index].config) {
                copy[index].config = { limits: [] }
            }
            if (!Array.isArray(copy[index].config.limits)) {
                copy[index].config.limits = []
            }
            copy[index].config.limits.push({ field: '', max: 1 })
            this.emitUpdate(copy)
        },

        removeLimit(index, limitIndex) {
            const copy = this.cloneRequirements()
            const limits = copy[index]?.config?.limits || []
            limits.splice(limitIndex, 1)
            copy[index].config.limits = limits
            this.emitUpdate(copy)
        },

        updateLimitField(index, limitIndex, value) {
            const copy = this.cloneRequirements()
            if (!copy[index].config) {
                copy[index].config = { limits: [] }
            }
            if (!Array.isArray(copy[index].config.limits)) {
                copy[index].config.limits = []
            }
            if (!copy[index].config.limits[limitIndex]) {
                copy[index].config.limits[limitIndex] = { field: '', max: 1 }
            }
            copy[index].config.limits[limitIndex].field = value
            this.emitUpdate(copy)
        },

        updateLimitMax(index, limitIndex, value) {
            const max = value ? parseInt(value) : null
            if (max != null && max < 1) return
            const copy = this.cloneRequirements()
            if (!copy[index].config) {
                copy[index].config = { limits: [] }
            }
            if (!Array.isArray(copy[index].config.limits)) {
                copy[index].config.limits = []
            }
            if (!copy[index].config.limits[limitIndex]) {
                copy[index].config.limits[limitIndex] = { field: '', max: 1 }
            }
            copy[index].config.limits[limitIndex].max = max
            this.emitUpdate(copy)
        },

        moveRequirement(index, direction) {
            const sorted = this.sortedRequirements
            const current = sorted[index]
            const target = sorted[index + direction]
            
            if (!current || !target) return

            const copy = this.cloneRequirements()

            const currentIndex = copy.findIndex(r => r === current)
            const targetIndex = copy.findIndex(r => r === target)

            if (currentIndex === -1 || targetIndex === -1) return

            // 🔑 Swap step_order values
            const temp = copy[currentIndex].step_order
            copy[currentIndex].step_order = copy[targetIndex].step_order
            copy[targetIndex].step_order = temp

            this.emitUpdate(this.normalize(copy))
            this.showSuccess('Form order updated')
        },

        availableFormTypeOptions(index) {
            const sorted = this.sortedRequirements[index]
            const used = this.requirements
                .filter(r => r !== sorted)
                .map(r => r.form_type)
                .filter(Boolean)

            return this.formTypeOptions.filter(o => !used.includes(o.value))
        },
    },

    mounted() {
        this.emitUpdate(this.normalize(this.cloneRequirements()))
    },

    beforeUnmount() {
        clearTimeout(this.successTimeout)
    },
}
</script>


<template>
    <div class="px-1 flex flex-col gap-2">
        <label class="font-bold uppercase flex items-center gap-2" title="Configure required forms for this event">
            <span>Requirements:</span>
            <transition name="fade">
                <div v-if="successMessage" class="p-0.5 bg-green-100 border border-green-300 rounded text-xs text-green-800 w-full">
                    ✓ {{ successMessage }}
                </div>
            </transition>
        </label>
        <transition-container type="slide-bottom">
            <div>
                <InputError v-show="!!error" class="" :message="error" />
                <InputError v-show="localErrors.requirements" class="" :message="localErrors.requirements" />
            </div>
        </transition-container>

        <!-- Requirements List -->
        <div class="space-y-2">
            <div
                v-for="(req, index) in sortedRequirements"
                :key="req.id || index"
                class="relative flex flex-col gap-1 border border-gray-200 dark:border-gray-700 rounded-md p-2 bg-white dark:bg-gray-900"
                :class="{ 'border-red-400': localErrors[`req_${index}`] }"
            >
                <!-- Step Header -->
                <div class="flex w-full justify-between">
                    <span class="text-xs font-semibold">STEP {{ req.step_order ?? index + 1 }}</span>
                    <div class="flex gap-1">
                        <div class="flex items-center gap-1 text-xs border p-0.5 bg-gray-50 dark:bg-gray-800 rounded opacity-100">
                            <input 
                                type="checkbox" 
                                :checked="req.is_enabled !== false" 
                                @change="toggleEnabled(index)" 
                                class="rounded-full" 
                                title="Enable/disable this form"
                            />
                            <span>{{ req.is_enabled !== false ? 'Enabled' : 'Disabled' }}</span>
                        </div>
                        <div class="flex items-center gap-1 text-xs" :class="{'opacity-50': req.is_enabled === false}">
                            <button 
                                type="button" 
                                class="px-2 py-1 border rounded flex items-center gap-1 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                                @click="moveRequirement(index, -1)"
                                :disabled="index === 0 || !req.is_enabled"
                                title="Move up"
                            >
                                <caret-down class="w-3 h-3 transform -rotate-180" />
                                Up
                            </button>
                            <button 
                                type="button" 
                                class="px-2 py-1 border rounded flex items-center gap-1 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                                @click="moveRequirement(index, 1)"
                                :disabled="index === sortedRequirements.length - 1 || !req.is_enabled"
                                title="Move down"
                            >
                                <caret-down class="w-3 h-3 transform" />
                                Down
                            </button>
                            <button
                                type="button" 
                                class="px-2 py-1 border rounded flex items-center gap-1 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                                :disabled="!req?.is_enabled"
                                @click="addLimit(index)"
                            >
                                <add-icon class="w-3 h-3" />
                                Add Limit
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Error for this requirement -->
                <transition name="slide-down">
                    <InputError v-if="localErrors[`req_${index}`]" :message="localErrors[`req_${index}`]" class="text-xs" />
                </transition>

                <!-- Form Fields -->
                <div class="flex flex-col gap-1" :class="{'opacity-50': req.is_enabled === false}">
                    <div class="grid grid-cols-3 justify-center items-center gap-2">
                        <!-- Left Column: Form Type and Max Slots -->
                        <div class="grid grid-rows-2 gap-2">
                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] text-gray-500">Form Type *</label>
                                <select
                                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm text-xs py-0.5 px-2 transition"
                                    :class="{ 'border-red-500': localErrors[`req_${index}_type`] }"
                                    :value="req.form_type || ''"
                                    :disabled="!req?.is_enabled"
                                    @change="handleTypeChange(index, $event.target.value)"
                                    title="Select the type of form"
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
                                <transition name="slide-down">
                                    <InputError 
                                        v-if="localErrors[`req_${index}_type`]" 
                                        :message="localErrors[`req_${index}_type`]" 
                                        class="text-xs" 
                                    />
                                </transition>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[11px] text-gray-500">Max Slots (optional)</label>
                                <input
                                    type="number"
                                    min="0"
                                    placeholder="No limit"
                                    :value="req.max_slots || ''"
                                    :disabled="!req?.is_enabled"
                                    @change="updateMaxSlots(index, $event.target.value)"
                                    class="text-xs p-0 px-2 rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 transition"
                                    :class="{ 'border-red-500': localErrors[`req_${index}_slots`] }"
                                    title="Maximum number of participants allowed"
                                />
                                <transition name="slide-down">
                                    <InputError 
                                        v-if="localErrors[`req_${index}_slots`]" 
                                        :message="localErrors[`req_${index}_slots`]" 
                                        class="text-xs" 
                                    />
                                </transition>
                            </div>
                        </div>

                        <!-- Middle Column: Open/Close Times -->
                        <div class="grid grid-rows-2 items-center gap-2 text-[11px] mt-1 w-full">
                            <div class="flex flex-col items-start gap-1">
                                <span class="text-gray-500">Open:</span>
                                <input
                                    type="datetime-local"
                                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm text-[11px] px-1 py-0.5 border transition"
                                    :class="{ 'border-red-500': localErrors[`req_${index}_from`] }"
                                    :value="req.open_from || ''"
                                    :disabled="!req.is_enabled"
                                    @change="updateOpenFrom(index, $event.target.value)"
                                    title="When this form becomes available"
                                />
                                <transition name="slide-down">
                                    <InputError 
                                        v-if="localErrors[`req_${index}_from`]" 
                                        :message="localErrors[`req_${index}_from`]" 
                                        class="text-xs" 
                                    />
                                </transition>
                            </div>
                            <div class="flex flex-col items-start gap-1">
                                <span class="text-gray-500">Close:</span>
                                <input
                                    type="datetime-local"
                                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm text-[11px] px-1 py-0.5 border transition"
                                    :class="{ 'border-red-500': localErrors[`req_${index}_to`] }"
                                    :value="req.open_to || ''"
                                    :disabled="!req?.is_enabled"
                                    @change="updateOpenTo(index, $event.target.value)"
                                    title="When this form is no longer available"
                                />
                                <transition name="slide-down">
                                    <InputError 
                                        v-if="localErrors[`req_${index}_to`]" 
                                        :message="localErrors[`req_${index}_to`]" 
                                        class="text-xs" 
                                    />
                                </transition>
                            </div>
                        </div>

                        <!-- Right Column: Required and Remove -->
                        <div class="flex items-end justify-center gap-1 flex-col">
                            <div class="flex items-center gap-1 text-xs bg-gray-50 dark:bg-gray-800 p-2 rounded">
                                <input 
                                    type="checkbox" 
                                    :checked="req.is_required" 
                                    @change="toggleRequired(index)" 
                                    class="rounded-full"
                                    :disabled="!req?.is_enabled"
                                    title="Mark as required for completion"
                                />
                                <span>{{ req.is_required ? 'Required' : 'Optional' }}</span>
                            </div>
                            <button
                                type="button"
                                class="text-xs text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900 px-2 py-1 rounded transition mt-1"
                                @click="removeRequirement(index)"
                                :disabled="!req?.is_enabled"
                                title="Remove this form"
                            >
                                ✕ Remove
                            </button>
                        </div>
                    </div>

                    <div v-if="req?.config?.limits?.length" class="mt-2 border-t pt-2" :class="{'opacity-50': req.is_enabled === false}">
                        <div class="flex items-center justify-between">
                            <label class="text-[11px] text-gray-500 uppercase">Conditional Limits</label>
                            <span class="text-[10px] text-gray-400">Limit submissions per field value</span>
                        </div>

                        <div class="mt-2 flex flex-col gap-2">
                            <div
                                v-for="(limit, limitIndex) in req.config.limits"
                                :key="`${index}-limit-${limitIndex}`"
                                class="grid grid-cols-3 gap-2 items-center"
                            >
                                <div class="flex flex-col gap-1">
                                    <label class="text-[10px] text-gray-500">Column</label>
                                    <input
                                        list="limit-field-options"
                                        type="text"
                                        class="text-xs p-0.5 px-2 rounded-md border border-gray-300"
                                        :value="limit.field"
                                        :disabled="!req?.is_enabled"
                                        @change="updateLimitField(index, limitIndex, $event.target.value)"
                                        placeholder="e.g. province_address"
                                    />
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-[10px] text-gray-500">Max per value</label>
                                    <input
                                        type="number"
                                        min="1"
                                        class="text-xs p-0.5 px-2 rounded-md border border-gray-300"
                                        :value="limit.max"
                                        :disabled="!req?.is_enabled"
                                        @change="updateLimitMax(index, limitIndex, $event.target.value)"
                                        placeholder="Max"
                                    />
                                </div>
                                <div class="flex items-end justify-end">
                                    <button
                                        type="button"
                                        class="text-[11px] text-red-500 hover:text-red-700"
                                        :disabled="!req?.is_enabled"
                                        @click="removeLimit(index, limitIndex)"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <datalist id="limit-field-options">
                            <option v-for="opt in limitFieldOptions" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </datalist>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!requirements.length" class="p-4 text-center text-gray-400 border border-dashed border-gray-300 rounded">
                <p>No forms added yet. Click "Add a form" to get started.</p>
            </div>
        </div>

        <!-- Add Button -->
        <button
            v-if="availableFormTypeOptions().length"
            type="button"
            class="mt-1 inline-flex items-center px-2 py-1 text-xs border border-dashed border-gray-400 rounded-md text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800 transition"
            @click="addRequirement"
            title="Add a new form to this event"
        >
            + Add a form
        </button>
        <div v-else class="text-xs text-gray-400 italic">All form types have been used.</div>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

.slide-down-enter-active, .slide-down-leave-active {
    transition: all 0.2s ease;
}
.slide-down-enter-from {
    opacity: 0;
    transform: translateY(-5px);
}
.slide-down-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}
</style>
