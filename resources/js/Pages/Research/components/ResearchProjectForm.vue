<script>
import { router } from '@inertiajs/vue3'
import ApiMixin from '@/Modules/mixins/ApiMixin'
import DtoResponse from '@/Modules/dto/DtoResponse'
import ResearchProject from '@/Modules/domain/ResearchProject'

export default {
    name: 'ResearchProjectForm',
    mixins: [ApiMixin],
    props: {
        data: {
            type: Object,
            default: null,
        },
        catalog: {
            type: Object,
            default: () => ({}),
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
    },
    beforeMount() {
        this.model = new ResearchProject(this.data ?? {})
        this.initializeForm()
    },
    methods: {
        initializeForm() {
            const action = this.isEdit ? 'update' : 'create'
            const payload = this.isEdit
                ? this.model.updateFields(this.data)
                : this.model.createFields()

            this.form = this.createFormWithRemember(payload, action)
        },
        async submitProxy() {
            const response = this.isEdit
                ? await this.submitUpdate()
                : await this.submitCreate()

            if (response instanceof DtoResponse) {
                const projectId = response?.data?.data?.id ?? this.data?.id
                router.visit(route('research.projects.show', projectId))
            }
        },
        async deleteProxy() {
            if (!this.isEdit) {
                return
            }

            if (!window.confirm('Delete this project and all of its studies, experiments, samples, and records?')) {
                return
            }

            this.toDelete = { id: this.data?.id }
            const response = await this.submitDelete()

            if (response instanceof DtoResponse) {
                router.visit(route('research.projects.index'))
            }
        },
    },
}
</script>

<template>
    <form v-if="form" class="space-y-6 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm" @submit.prevent="submitProxy">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-gray-100 pb-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">{{ isEdit ? 'Update Project' : 'New Project' }}</p>
                <h2 class="mt-2 text-2xl font-semibold text-gray-900">{{ isEdit ? 'Project update form' : 'Create research project' }}</h2>
                <p class="mt-2 text-sm leading-6 text-gray-600">Capture the project profile before attaching studies, experiments, and sample monitoring work.</p>
            </div>
            <div class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                {{ isEdit ? 'Editing active project' : 'Create mode' }}
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-gray-700">Project title</label>
                <input v-model="form.title" class="w-full rounded-xl border-gray-300" placeholder="Ex. Marker-assisted drought tolerance breeding" />
                <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Primary commodity</label>
                <input v-model="form.commodity" list="research-project-form-commodity-options" class="w-full rounded-xl border-gray-300" placeholder="Rice" />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Overall budget</label>
                <input v-model="form.overall_budget" type="number" min="0" step="0.01" class="w-full rounded-xl border-gray-300" placeholder="0.00" />
                <p v-if="form.errors.overall_budget" class="mt-1 text-xs text-red-600">{{ form.errors.overall_budget }}</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Duration start</label>
                <input v-model="form.duration_start" type="date" class="w-full rounded-xl border-gray-300" />
                <p v-if="form.errors.duration_start" class="mt-1 text-xs text-red-600">{{ form.errors.duration_start }}</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Duration end</label>
                <input v-model="form.duration_end" type="date" class="w-full rounded-xl border-gray-300" />
                <p v-if="form.errors.duration_end" class="mt-1 text-xs text-red-600">{{ form.errors.duration_end }}</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Funding agency</label>
                <input v-model="form.funding_agency" class="w-full rounded-xl border-gray-300" placeholder="Agency or donor" />
                <p v-if="form.errors.funding_agency" class="mt-1 text-xs text-red-600">{{ form.errors.funding_agency }}</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Funding code</label>
                <input v-model="form.funding_code" class="w-full rounded-xl border-gray-300" placeholder="Code or grant number" />
                <p v-if="form.errors.funding_code" class="mt-1 text-xs text-red-600">{{ form.errors.funding_code }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-gray-700">Project leader</label>
                <select v-model="form.project_leader_id" class="w-full rounded-xl border-gray-300">
                    <option value="">Select a research user</option>
                    <option v-for="option in researchUserOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
                <p class="mt-2 text-xs text-gray-500">Options are limited to users with Researcher or Research Supervisor roles.</p>
                <p v-if="form.errors.project_leader_id" class="mt-1 text-xs text-red-600">{{ form.errors.project_leader_id }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-gray-700">Objective</label>
                <textarea v-model="form.objective" rows="5" class="w-full rounded-xl border-gray-300" placeholder="Summarize the major objective, breeding goal, or experimental direction." />
                <p v-if="form.errors.objective" class="mt-1 text-xs text-red-600">{{ form.errors.objective }}</p>
            </div>
        </div>

        <div class="flex flex-wrap justify-end gap-3">
            <button v-if="showCancelButton" type="button" class="rounded-xl border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="$emit('cancel')">
                Cancel
            </button>
            <button v-if="isEdit && showDeleteButton" type="button" :disabled="processing" class="rounded-xl border border-red-200 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50" @click="deleteProxy">
                Delete Project
            </button>
            <button :disabled="processing" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                {{ processing ? 'Saving...' : (isEdit ? 'Save Project' : 'Create Project') }}
            </button>
        </div>

        <datalist id="research-project-form-commodity-options">
            <option v-for="commodity in catalog.commodities || []" :key="commodity" :value="commodity" />
        </datalist>
    </form>
</template>