<script>
import { router } from '@inertiajs/vue3'
import ApiMixin from '@/Modules/mixins/ApiMixin'
import DtoResponse from '@/Modules/dto/DtoResponse'
import ResearchStudy from '@/Modules/domain/ResearchStudy'

export default {
    name: 'ResearchStudyForm',
    mixins: [ApiMixin],
    props: {
        data: {
            type: Object,
            default: null,
        },
        project: {
            type: Object,
            default: null,
        },
        researchUsers: {
            type: Array,
            default: () => [],
        },
        showCancelButton: {
            type: Boolean,
            default: false,
        },
        showDeleteButton: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['cancel'],
    computed: {
        isEdit() {
            return !!this.data
        },
        researchUserOptions() {
            return (this.researchUsers || []).map((user) => ({
                value: user.id,
                label: `${user.name} (${user.position})`,
            }))
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
                : {
                    ...this.model.createFields(),
                    project_id: this.parentProjectId,
                }

            this.form = this.createFormWithRemember(payload, action)
        },
        async submitProxy() {
            const response = this.isEdit
                ? await this.submitUpdate()
                : await this.submitCreate()

            if (response instanceof DtoResponse) {
                const studyId = response?.data?.data?.id ?? this.data?.id
                router.visit(route('research.studies.show', studyId))
            }
        },
        async deleteProxy() {
            if (!this.isEdit) {
                return
            }

            if (!window.confirm('Delete this study and all linked experiments?')) {
                return
            }

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
    <form v-if="form" class="space-y-6 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm" @submit.prevent="submitProxy">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-gray-100 pb-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">{{ isEdit ? 'Update Study' : 'New Study' }}</p>
                <h2 class="mt-2 text-2xl font-semibold text-gray-900">{{ isEdit ? 'Study update form' : 'Create project study' }}</h2>
                <p class="mt-2 text-sm leading-6 text-gray-600">Use a dedicated study profile to define its leadership, budget, and experiment scope before launching experiments.</p>
            </div>
            <div class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                {{ isEdit ? 'Editing active study' : 'Create mode' }}
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-gray-700">Study title</label>
                <input v-model="form.title" class="w-full rounded-xl border-gray-300" placeholder="Study title" />
                <p v-if="fieldError('title')" class="mt-1 text-xs text-red-600">{{ fieldError('title') }}</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Study budget</label>
                <input v-model="form.budget" type="number" min="0" step="0.01" class="w-full rounded-xl border-gray-300" placeholder="0.00" />
                <p v-if="fieldError('budget')" class="mt-1 text-xs text-red-600">{{ fieldError('budget') }}</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Study leader</label>
                <select v-model="form.study_leader_id" class="w-full rounded-xl border-gray-300">
                    <option value="">Select a research user</option>
                    <option v-for="option in researchUserOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
                <p v-if="fieldError('study_leader_id')" class="mt-1 text-xs text-red-600">{{ fieldError('study_leader_id') }}</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Supervisor</label>
                <select v-model="form.supervisor_id" class="w-full rounded-xl border-gray-300">
                    <option value="">Select a research user</option>
                    <option v-for="option in researchUserOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
                <p v-if="fieldError('supervisor_id')" class="mt-1 text-xs text-red-600">{{ fieldError('supervisor_id') }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-gray-700">Study objective</label>
                <textarea v-model="form.objective" rows="4" class="w-full rounded-xl border-gray-300" />
                <p v-if="fieldError('objective')" class="mt-1 text-xs text-red-600">{{ fieldError('objective') }}</p>
            </div>

            <div class="md:col-span-2">
                <MultiSelectDropdown
                    v-model="form.staff_member_ids"
                    :options="researchUserOptions"
                    label="Study Staff"
                    placeholder="Select study staff"
                />
                <p v-if="fieldError('staff_member_ids')" class="mt-1 text-xs text-red-600">{{ fieldError('staff_member_ids') }}</p>
            </div>
        </div>

        <div class="flex flex-wrap justify-end gap-3">
            <button v-if="showCancelButton" type="button" class="rounded-xl border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="$emit('cancel')">
                Cancel
            </button>
            <button v-if="isEdit && showDeleteButton" type="button" :disabled="processing" class="rounded-xl border border-red-200 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50" @click="deleteProxy">
                Delete Study
            </button>
            <button :disabled="processing" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                {{ processing ? 'Saving...' : (isEdit ? 'Save Study' : 'Create Study') }}
            </button>
        </div>
    </form>
</template>