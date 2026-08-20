<script>
import { toRaw } from 'vue'
import axios from 'axios'

export default {
    name: 'RequirementsManager',

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
            customTemplates: [],
            loadingTemplates: false,
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

        systemFormTypeOptions() {
            return [
                { value: 'preregistration', label: 'Pre-registration', isSystem: true },
                { value: 'preregistration_biotech', label: 'Pre-registration + Quiz Bee', isSystem: true },
                { value: 'preregistration_quizbee', label: 'Pre-registration Quiz Bee', isSystem: true },
                { value: 'registration', label: 'Registration / Attendance', isSystem: true },
                { value: 'pretest', label: 'Pre-test', isSystem: true },
                { value: 'posttest', label: 'Post-test', isSystem: true },
                { value: 'feedback', label: 'Feedback / Evaluation', isSystem: true },
            ]
        },
        customFormTypeOptions() {
            return this.customTemplates.map(t => ({
                value: `custom:${t.id}`,
                label: `${t.name}`,
                templateId: t.id,
                isCustom: true,
                fieldCount: t.field_definitions_count || 0,
            }))
        },
        formTypeOptions() {
            return [...this.systemFormTypeOptions, ...this.customFormTypeOptions]
        },
        limitFieldOptions() {
            return [
                { value: 'region_address', label: 'Region' },
                { value: 'province_address', label: 'Province' },
                { value: 'city_address', label: 'City' },
                { value: 'organization', label: 'Organization' },
                { value: 'designation', label: 'Designation/Position' },
                { value: 'attendance_type', label: 'Attendance Type' },
                { value: 'sex', label: 'Sex' },
                { value: 'age', label: 'Age' },
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

        getActualIndex(sortedIndex) {
            const sorted = this.sortedRequirements[sortedIndex]
            return this.requirements.indexOf(sorted)
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

        handleTypeChange(sortedIndex, value) {
            if (!value) {
                this.setError(`req_${sortedIndex}_type`, 'Form type is required')
                return
            }

            const actualIndex = this.getActualIndex(sortedIndex)
            if (actualIndex === -1) return

            const copy = this.cloneRequirements()

            if (copy.some((r, i) => i !== actualIndex && r.form_type === value)) {
                this.setError(`req_${sortedIndex}_type`, `Form type "${value}" already used`)
                return
            }

            const isCustom = value.startsWith('custom:')
            const templateId = isCustom ? value.replace('custom:', '') : null
            const formType = isCustom ? 'custom' : value

            copy[actualIndex] = {
                ...copy[actualIndex],
                form_type: formType,
                form_type_template_id: templateId,
                step_type: copy[actualIndex].step_type ?? formType,
                id: null,
                config: copy[actualIndex].config ?? { limits: [] },
            }

            this.clearError(`req_${sortedIndex}_type`)
            this.emitUpdate(this.normalize(copy))
            this.showSuccess('Form type updated')
        },

        async loadCustomTemplates() {
            this.loadingTemplates = true
            try {
                const response = await axios.get(route('api.form-builder.templates.index'))
                this.customTemplates = (response.data?.data || []).filter(t => !t.is_system)
            } catch (err) {
                console.error('Failed to load custom templates:', err)
            } finally {
                this.loadingTemplates = false
            }
        },

        getFormTypeValue(req) {
            if (req.form_type === 'custom' && req.form_type_template_id) {
                return `custom:${req.form_type_template_id}`
            }
            return req.form_type || ''
        },

        getTemplateInfo(req) {
            if (req.form_type !== 'custom' || !req.form_type_template_id) return null
            return this.customTemplates.find(t => t.id === req.form_type_template_id)
        },

        toggleRequired(sortedIndex) {
            const actualIndex = this.getActualIndex(sortedIndex)
            if (actualIndex === -1) return

            const copy = this.cloneRequirements()
            copy[actualIndex].is_required = !copy[actualIndex].is_required
            this.emitUpdate(copy)
            this.showSuccess(copy[actualIndex].is_required ? 'Form marked as required' : 'Form marked as optional')
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

        updateMaxSlots(sortedIndex, value) {
            const actualIndex = this.getActualIndex(sortedIndex)
            if (actualIndex === -1) return

            const num = value ? parseInt(value) : null
            if (num != null && num < 0) return

            const copy = this.cloneRequirements()
            copy[actualIndex].max_slots = num
            this.emitUpdate(copy)
            this.showSuccess('Max slots updated')
        },

        updateOpenFrom(sortedIndex, value) {
            const actualIndex = this.getActualIndex(sortedIndex)
            if (actualIndex === -1) return

            const copy = this.cloneRequirements()
            copy[actualIndex].open_from = value || null
            this.emitUpdate(copy)
            this.showSuccess('Open from date updated')
        },

        updateOpenTo(sortedIndex, value) {
            const actualIndex = this.getActualIndex(sortedIndex)
            if (actualIndex === -1) return

            const copy = this.cloneRequirements()
            copy[actualIndex].open_to = value || null
            this.emitUpdate(copy)
            this.showSuccess('Open to date updated')
        },

        addLimit(sortedIndex) {
            const actualIndex = this.getActualIndex(sortedIndex)
            if (actualIndex === -1) return

            const copy = this.cloneRequirements()
            if (!copy[actualIndex].config) {
                copy[actualIndex].config = { limits: [] }
            }
            if (!Array.isArray(copy[actualIndex].config.limits)) {
                copy[actualIndex].config.limits = []
            }
            copy[actualIndex].config.limits.push({ field: '', max: 1 })
            this.emitUpdate(copy)
        },

        removeLimit(sortedIndex, limitIndex) {
            const actualIndex = this.getActualIndex(sortedIndex)
            if (actualIndex === -1) return

            const copy = this.cloneRequirements()
            const limits = copy[actualIndex]?.config?.limits || []
            limits.splice(limitIndex, 1)
            copy[actualIndex].config.limits = limits
            this.emitUpdate(copy)
        },

        updateLimitField(sortedIndex, limitIndex, value) {
            const actualIndex = this.getActualIndex(sortedIndex)
            if (actualIndex === -1) return

            const copy = this.cloneRequirements()
            if (!copy[actualIndex].config) {
                copy[actualIndex].config = { limits: [] }
            }
            if (!Array.isArray(copy[actualIndex].config.limits)) {
                copy[actualIndex].config.limits = []
            }
            if (!copy[actualIndex].config.limits[limitIndex]) {
                copy[actualIndex].config.limits[limitIndex] = { field: '', max: 1 }
            }
            copy[actualIndex].config.limits[limitIndex].field = value
            this.emitUpdate(copy)
        },

        updateLimitMax(sortedIndex, limitIndex, value) {
            const actualIndex = this.getActualIndex(sortedIndex)
            if (actualIndex === -1) return

            const max = value ? parseInt(value) : null
            if (max != null && max < 1) return
            const copy = this.cloneRequirements()
            if (!copy[actualIndex].config) {
                copy[actualIndex].config = { limits: [] }
            }
            if (!Array.isArray(copy[actualIndex].config.limits)) {
                copy[actualIndex].config.limits = []
            }
            if (!copy[actualIndex].config.limits[limitIndex]) {
                copy[actualIndex].config.limits[limitIndex] = { field: '', max: 1 }
            }
            copy[actualIndex].config.limits[limitIndex].max = max
            this.emitUpdate(copy)
        },

        moveRequirement(index, direction) {
            const targetSortedIndex = index + direction
            if (targetSortedIndex < 0 || targetSortedIndex >= this.sortedRequirements.length) return

            const currentIndex = this.getActualIndex(index)
            const targetIndex = this.getActualIndex(targetSortedIndex)

            if (currentIndex === -1 || targetIndex === -1) return

            const copy = this.cloneRequirements()

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
                .map(r => {
                    if (r.form_type === 'custom' && r.form_type_template_id) {
                        return `custom:${r.form_type_template_id}`
                    }
                    return r.form_type
                })
                .filter(Boolean)

            return this.formTypeOptions.filter(o => !used.includes(o.value))
        },
    },

    mounted() {
        this.emitUpdate(this.normalize(this.cloneRequirements()))
        this.loadCustomTemplates()
    },

    beforeUnmount() {
        clearTimeout(this.successTimeout)
    },
}
</script>

<template>
    <div class="flex flex-col gap-4">
        
        <!-- Alerts -->
        <transition-container type="slide-bottom">
            <div v-if="error || localErrors.requirements" class="flex flex-col gap-2">
                <div v-if="error" class="flex items-center gap-2 p-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-xl text-red-700 dark:text-red-400 text-sm font-semibold shadow-sm">
                    <LuAlertCircle class="w-4 h-4 shrink-0" /> {{ error }}
                </div>
                <div v-if="localErrors.requirements" class="flex items-center gap-2 p-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-xl text-red-700 dark:text-red-400 text-sm font-semibold shadow-sm">
                    <LuAlertCircle class="w-4 h-4 shrink-0" /> {{ localErrors.requirements }}
                </div>
            </div>
        </transition-container>

        <!-- Requirements List -->
        <div class="space-y-4">
            <div
                v-for="(req, index) in sortedRequirements"
                :key="req.id || index"
                class="group relative flex flex-col gap-3 border rounded-2xl p-4 sm:p-5 transition-all duration-300"
                :class="[
                    localErrors[`req_${index}`] ? 'border-red-400 dark:border-red-500/50 bg-red-50/30 dark:bg-red-900/10' : 'border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40 hover:border-indigo-300 dark:hover:border-indigo-500/40 hover:shadow-md'
                ]"
            >
                <!-- Step Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/60 dark:border-slate-700/60 pb-3">
                    <div class="flex items-center gap-2.5 ml-1">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold shrink-0" :class="req.is_enabled !== false ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-400' : 'bg-slate-200 text-slate-500 dark:bg-slate-700'">
                            {{ req.step_order ?? index + 1 }}
                        </span>
                        <span class="text-sm font-bold uppercase tracking-widest text-slate-700 dark:text-slate-300">Step {{ req.step_order ?? index + 1 }}</span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Toggles -->
                        <label class="flex items-center gap-2 px-2.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg cursor-pointer transition-colors shadow-sm">
                            <input 
                                type="checkbox" 
                                :checked="req.is_enabled !== false" 
                                @change="toggleEnabled(index)" 
                                class="w-3.5 h-3.5 text-indigo-600 rounded border-slate-300 dark:border-slate-600 focus:ring-indigo-500 bg-slate-100 dark:bg-slate-800" 
                            />
                            <span class="text-[0.65rem] font-bold uppercase tracking-widest" :class="req.is_enabled !== false ? 'text-slate-700 dark:text-slate-300' : 'text-slate-400 dark:text-slate-500'">{{ req.is_enabled !== false ? 'Enabled' : 'Disabled' }}</span>
                        </label>
                        
                        <label class="flex items-center gap-2 px-2.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg cursor-pointer transition-colors shadow-sm" :class="{ 'opacity-50 cursor-not-allowed': req.is_enabled === false }">
                            <input 
                                type="checkbox" 
                                :checked="req.is_required" 
                                @change="toggleRequired(index)" 
                                :disabled="!req?.is_enabled"
                                class="w-3.5 h-3.5 text-indigo-600 rounded border-slate-300 dark:border-slate-600 focus:ring-indigo-500 bg-slate-100 dark:bg-slate-800" 
                            />
                            <span class="text-[0.65rem] font-bold uppercase tracking-widest" :class="req.is_required ? 'text-slate-700 dark:text-slate-300' : 'text-slate-400 dark:text-slate-500'">{{ req.is_required ? 'Required' : 'Optional' }}</span>
                        </label>

                        <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 mx-1 hidden sm:block"></div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-1.5" :class="{'opacity-50': req.is_enabled === false}">
                            <button 
                                type="button" 
                                @click="moveRequirement(index, -1)"
                                :disabled="index === 0 || !req.is_enabled"
                                class="p-1.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg text-slate-500 transition-colors disabled:opacity-50"
                                title="Move up"
                            >
                                <LuChevronDown class="w-4 h-4 rotate-180" />
                            </button>
                            <button 
                                type="button" 
                                @click="moveRequirement(index, 1)"
                                :disabled="index === sortedRequirements.length - 1 || !req.is_enabled"
                                class="p-1.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg text-slate-500 transition-colors disabled:opacity-50"
                                title="Move down"
                            >
                                <LuChevronDown class="w-4 h-4" />
                            </button>
                            <button
                                type="button" 
                                :disabled="!req?.is_enabled"
                                @click="addLimit(index)"
                                class="p-1.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-200 dark:hover:border-indigo-500/30 rounded-lg text-slate-500 transition-colors disabled:opacity-50"
                                title="Add Limit"
                            >
                                <LuSettings2 class="w-4 h-4" />
                            </button>
                            <button
                                type="button"
                                @click="removeRequirement(index)"
                                :disabled="!req?.is_enabled"
                                class="p-1.5 ml-1 border border-transparent hover:border-red-200 dark:hover:border-red-500/30 bg-transparent hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg text-red-400 hover:text-red-600 dark:hover:text-red-400 transition-colors disabled:opacity-50"
                                title="Remove this form"
                            >
                                <LuTrash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Error for this requirement -->
                <transition name="slide-down">
                    <InputError v-if="localErrors[`req_${index}`]" :message="localErrors[`req_${index}`]" class="text-xs" />
                </transition>

                <!-- Form Fields Grid -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-start mt-2" :class="{'opacity-60 grayscale-[0.2] pointer-events-none': req.is_enabled === false}">
                    
                    <!-- Form Type (Full width on mobile, spans 4 cols on desktop) -->
                    <div class="md:col-span-5">
                        <label class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1.5 block">Form Type *</label>
                        <select
                            class="w-full border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-xl shadow-sm text-sm py-2.5 px-3 transition-colors focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-medium text-slate-800 dark:text-slate-200"
                            :class="{ 'border-red-500 ring-1 ring-red-500': localErrors[`req_${index}_type`] }"
                            :value="getFormTypeValue(req)"
                            :disabled="!req?.is_enabled || loadingTemplates"
                            @change="handleTypeChange(index, $event.target.value)"
                        >
                            <option value="" disabled>{{ loadingTemplates ? 'Loading...' : 'Select form type...' }}</option>
                            <optgroup label="System Forms">
                                <option v-for="opt in availableFormTypeOptions(index).filter(o => o.isSystem)" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </optgroup>
                            <optgroup v-if="customFormTypeOptions.length" label="Custom Templates">
                                <option v-for="opt in availableFormTypeOptions(index).filter(o => o.isCustom)" :key="opt.value" :value="opt.value">
                                    {{ opt.label }} ({{ opt.fieldCount }} fields)
                                </option>
                            </optgroup>
                        </select>
                        <div v-if="getTemplateInfo(req)" class="text-[0.65rem] font-bold uppercase tracking-widest mt-1.5 flex items-center gap-1 text-indigo-600 dark:text-indigo-400">
                            <LuFileCode2 class="w-3.5 h-3.5" /> Custom: {{ getTemplateInfo(req)?.name }}
                        </div>
                        <transition name="slide-down">
                            <InputError v-if="localErrors[`req_${index}_type`]" :message="localErrors[`req_${index}_type`]" class="text-xs mt-1" />
                        </transition>
                    </div>

                    <!-- Timings & Limits Grid -->
                    <div class="md:col-span-7 grid grid-cols-2 sm:grid-cols-3 gap-4">
                        
                        <!-- Open Time -->
                        <div>
                            <label class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1.5 block">Opens At</label>
                            <input
                                type="datetime-local"
                                class="w-full border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-xl shadow-sm text-xs py-2.5 px-3 transition-colors focus:ring-2 focus:ring-indigo-500 font-medium text-slate-800 dark:text-slate-200"
                                :class="{ 'border-red-500 ring-1 ring-red-500': localErrors[`req_${index}_from`] }"
                                :value="req.open_from || ''"
                                :disabled="!req.is_enabled"
                                @change="updateOpenFrom(index, $event.target.value)"
                            />
                            <transition name="slide-down">
                                <InputError v-if="localErrors[`req_${index}_from`]" :message="localErrors[`req_${index}_from`]" class="text-xs mt-1" />
                            </transition>
                        </div>
                        
                        <!-- Close Time -->
                        <div>
                            <label class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1.5 block">Closes At</label>
                            <input
                                type="datetime-local"
                                class="w-full border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-xl shadow-sm text-xs py-2.5 px-3 transition-colors focus:ring-2 focus:ring-indigo-500 font-medium text-slate-800 dark:text-slate-200"
                                :class="{ 'border-red-500 ring-1 ring-red-500': localErrors[`req_${index}_to`] }"
                                :value="req.open_to || ''"
                                :disabled="!req?.is_enabled"
                                @change="updateOpenTo(index, $event.target.value)"
                            />
                            <transition name="slide-down">
                                <InputError v-if="localErrors[`req_${index}_to`]" :message="localErrors[`req_${index}_to`]" class="text-xs mt-1" />
                            </transition>
                        </div>

                        <!-- Max Slots -->
                        <div class="col-span-2 sm:col-span-1">
                            <label class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1.5 block">Max Slots <span class="opacity-70 normal-case tracking-normal font-medium">(opt)</span></label>
                            <input
                                type="number"
                                min="0"
                                placeholder="No limit"
                                :value="req.max_slots || ''"
                                :disabled="!req?.is_enabled"
                                @change="updateMaxSlots(index, $event.target.value)"
                                class="w-full border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-xl shadow-sm text-sm py-2.5 px-3 transition-colors focus:ring-2 focus:ring-indigo-500 font-medium text-slate-800 dark:text-slate-200"
                                :class="{ 'border-red-500 ring-1 ring-red-500': localErrors[`req_${index}_slots`] }"
                            />
                            <transition name="slide-down">
                                <InputError v-if="localErrors[`req_${index}_slots`]" :message="localErrors[`req_${index}_slots`]" class="text-xs mt-1" />
                            </transition>
                        </div>
                    </div>
                </div>

                <!-- Conditional Limits Section -->
                <div v-if="req?.config?.limits?.length" class="mt-4 pt-4 border-t border-dashed border-slate-300 dark:border-slate-700" :class="{'opacity-60 grayscale-[0.2] pointer-events-none': req.is_enabled === false}">
                    <div class="flex items-center gap-2 mb-3">
                        <LuListFilter class="w-4 h-4 text-indigo-500" />
                        <h4 class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-700 dark:text-slate-300">Conditional Limits</h4>
                        <span class="text-xs font-medium text-slate-400 dark:text-slate-500 ml-1">Limit submissions based on specific field values.</span>
                    </div>

                    <div class="flex flex-col gap-2.5">
                        <div
                            v-for="(limit, limitIndex) in req.config.limits"
                            :key="`${index}-limit-${limitIndex}`"
                            class="flex flex-col sm:flex-row sm:items-end gap-3 p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm"
                        >
                            <div class="flex-1">
                                <label class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1.5 block">Target Field</label>
                                <input
                                    list="limit-field-options"
                                    type="text"
                                    class="w-full border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 rounded-lg text-xs py-2 px-3 font-medium focus:ring-1 focus:ring-indigo-500 text-slate-800 dark:text-slate-200"
                                    :value="limit.field"
                                    :disabled="!req?.is_enabled"
                                    @change="updateLimitField(index, limitIndex, $event.target.value)"
                                    placeholder="e.g. province_address"
                                />
                            </div>
                            <div class="w-full sm:w-32">
                                <label class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1.5 block">Max per value</label>
                                <input
                                    type="number"
                                    min="1"
                                    class="w-full border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 rounded-lg text-xs py-2 px-3 font-medium focus:ring-1 focus:ring-indigo-500 text-slate-800 dark:text-slate-200"
                                    :value="limit.max"
                                    :disabled="!req?.is_enabled"
                                    @change="updateLimitMax(index, limitIndex, $event.target.value)"
                                    placeholder="Max limit"
                                />
                            </div>
                            <div class="flex items-center justify-end sm:justify-start w-full sm:w-auto shrink-0 pt-2 sm:pt-0 pb-1 sm:pb-0">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 sm:p-2 text-xs font-bold text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors"
                                    :disabled="!req?.is_enabled"
                                    @click="removeLimit(index, limitIndex)"
                                >
                                    <LuTrash2 class="w-4 h-4" /> <span class="sm:hidden">Remove Limit</span>
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

            <!-- Empty State -->
            <div v-if="!requirements.length" class="py-10 px-4 text-center border-2 border-dashed border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/20 rounded-2xl">
                <LuFilePlus2 class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">No forms attached yet.</p>
                <p class="text-xs font-medium text-slate-400 dark:text-slate-500 mt-1">Click the button below to add your first workflow step.</p>
            </div>
        </div>

        <!-- Add Button -->
        <div class="pt-2">
            <button
                v-if="availableFormTypeOptions().length"
                type="button"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/30 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 rounded-xl transition-colors active:scale-95 shadow-sm"
                @click="addRequirement"
            >
                <LuPlus class="w-4 h-4" /> Add Workflow Step
            </button>
            <div v-else class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 text-center py-4 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700">
                <LuCheckCircle2 class="w-4 h-4 inline-block mr-1.5 -mt-0.5 text-emerald-500" /> All available form types have been attached.
            </div>
        </div>
    </div>
</template>

<style scoped>
.slide-down-enter-active, .slide-down-leave-active {
    transition: all 0.2s ease-out;
}
.slide-down-enter-from {
    opacity: 0;
    transform: translateY(-8px);
}
.slide-down-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>