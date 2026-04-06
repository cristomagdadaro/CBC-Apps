<script>
import { router } from '@inertiajs/vue3'
import ApiMixin from '@/Modules/mixins/ApiMixin'
import DtoResponse from '@/Modules/dto/DtoResponse'
import ResearchStudy from '@/Modules/domain/ResearchStudy'

export default {
    name: 'ResearchStudyForm',
    mixins: [ApiMixin],
    props: {
        data: { type: Object, default: null },
        project: { type: Object, default: null },
        researchUsers: { type: Array, default: () => [] },
        showCancelButton: { type: Boolean, default: false },
        showDeleteButton: { type: Boolean, default: false },
    },
    emits: ['cancel'],
    computed: {
        isEdit() { return !!this.data },
        researchUserOptions() {
            return (this.researchUsers || []).map(u => ({ value: u.id, label: `${u.name} — ${u.position}` }))
        },
        parentProjectId() {
            return this.project?.id ?? this.data?.project_id ?? this.data?.project?.id ?? null
        },
    },
    beforeMount() {
        this.model = new ResearchStudy(this.data ?? {})
        this.initializeForm()
    },
    methods: {
        initializeForm() {
            const action = this.isEdit ? 'update' : 'create'
            const payload = this.isEdit
                ? this.model.updateFields(this.data)
                : { ...this.model.createFields(), project_id: this.parentProjectId }
            this.form = this.createFormWithRemember(payload, action)
        },
        async submitProxy() {
            const response = this.isEdit ? await this.submitUpdate() : await this.submitCreate()
            if (response instanceof DtoResponse) {
                router.visit(route('research.studies.show', response?.data?.data?.id ?? this.data?.id))
            }
        },
        async deleteProxy() {
            if (!this.isEdit || !window.confirm('Delete this study and all linked experiments? This cannot be undone.')) return
            this.toDelete = { id: this.data?.id }
            const response = await this.submitDelete()
            if (response instanceof DtoResponse) {
                router.visit(route('research.projects.show', this.parentProjectId))
            }
        },
        fieldError(field) {
            return this.form?.errors?.[field] || ''
        },
    },
}
</script>

<template>
    <form v-if="form" @submit.prevent="submitProxy" class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-100 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-900">
                {{ isEdit ? 'Edit Study' : 'Create New Study' }}
            </h3>
            <p class="mt-1 text-sm text-slate-500">
                {{ isEdit ? 'Update study details and team assignments.' : `Add a study under ${project?.title || 'selected project'}.` }}
            </p>
        </div>

        <div class="p-6 space-y-6">
            <div class="grid gap-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Study Title <span class="text-red-500">*</span></label>
                    <input v-model="form.title" 
                        class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                        placeholder="e.g., BC1F1 Population Development for Drought Tolerance" />
                    <p v-if="fieldError('title')" class="mt-1 text-xs text-red-600">{{ fieldError('title') }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Study Budget</label>
                    <div class="relative mt-1 rounded-md shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <span class="text-slate-500 sm:text-sm">$</span>
                        </div>
                        <input v-model="form.budget" type="number" min="0" step="0.01"
                            class="block w-full rounded-lg border-slate-300 pl-7 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                            placeholder="0.00" />
                    </div>
                    <p v-if="fieldError('budget')" class="mt-1 text-xs text-red-600">{{ fieldError('budget') }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Study Leader</label>
                    <select v-model="form.study_leader_id"
                        class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select researcher...</option>
                        <option v-for="opt in researchUserOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                    </select>
                    <p v-if="fieldError('study_leader_id')" class="mt-1 text-xs text-red-600">{{ fieldError('study_leader_id') }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Supervisor</label>
                    <select v-model="form.supervisor_id"
                        class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select supervisor...</option>
                        <option v-for="opt in researchUserOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                    </select>
                    <p v-if="fieldError('supervisor_id')" class="mt-1 text-xs text-red-600">{{ fieldError('supervisor_id') }}</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Study Objective</label>
                    <textarea v-model="form.objective" rows="4"
                        class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Define specific research objectives, target traits, and expected outcomes..." />
                    <p v-if="fieldError('objective')" class="mt-1 text-xs text-red-600">{{ fieldError('objective') }}</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Study Staff</label>
                    <MultiSelectDropdown v-model="form.staff_member_ids" :options="researchUserOptions"
                        label="Select team members" placeholder="Choose researchers..." />
                    <p v-if="fieldError('staff_member_ids')" class="mt-1 text-xs text-red-600">{{ fieldError('staff_member_ids') }}</p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50 rounded-b-xl">
            <button v-if="showCancelButton" type="button" @click="$emit('cancel')"
                class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                Cancel
            </button>
            <button v-if="isEdit && showDeleteButton" type="button" :disabled="processing" @click="deleteProxy"
                class="inline-flex items-center rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 shadow-sm hover:bg-red-50">
                <LuTrash2 class="mr-2 h-4 w-4" />
                Delete
            </button>
            <button type="submit" :disabled="processing"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50">
                <LuLoader2 v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                <LuSave v-else class="mr-2 h-4 w-4" />
                {{ processing ? 'Saving...' : (isEdit ? 'Save Changes' : 'Create Study') }}
            </button>
        </div>
    </form>
</template>