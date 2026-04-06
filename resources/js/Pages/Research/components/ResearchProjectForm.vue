<script>
import { router } from '@inertiajs/vue3'
import ApiMixin from '@/Modules/mixins/ApiMixin'
import DtoResponse from '@/Modules/dto/DtoResponse'
import ResearchProject from '@/Modules/domain/ResearchProject'

export default {
    name: 'ResearchProjectForm',
    mixins: [ApiMixin],
    props: {
        data: { type: Object, default: null },
        catalog: { type: Object, default: () => ({}) },
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
    },
    beforeMount() {
        this.model = new ResearchProject(this.data ?? {})
        this.initializeForm()
    },
    methods: {
        initializeForm() {
            const action = this.isEdit ? 'update' : 'create'
            const payload = this.isEdit ? this.model.updateFields(this.data) : this.model.createFields()
            this.form = this.createFormWithRemember(payload, action)
        },
        async submitProxy() {
            const response = this.isEdit ? await this.submitUpdate() : await this.submitCreate()
            if (response instanceof DtoResponse) {
                router.visit(route('research.projects.show', response?.data?.data?.id ?? this.data?.id))
            }
        },
        async deleteProxy() {
            if (!this.isEdit || !window.confirm('Delete this project and all associated data? This cannot be undone.')) return
            this.toDelete = { id: this.data?.id }
            const response = await this.submitDelete()
            if (response instanceof DtoResponse) router.visit(route('research.projects.index'))
        },
    },
}
</script>

<template>
    <form v-if="form" @submit.prevent="submitProxy" class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-100 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-900">
                {{ isEdit ? 'Edit Project' : 'Create New Project' }}
            </h3>
            <p class="mt-1 text-sm text-slate-500">
                {{ isEdit ? 'Update project details and settings.' : 'Define the project scope and leadership.' }}
            </p>
        </div>

        <div class="p-6 space-y-6">
            <div class="grid gap-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Project Title <span class="text-red-500">*</span></label>
                    <input v-model="form.title" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g., Drought Resistance Marker-Assisted Breeding" />
                    <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Primary Commodity</label>
                    <input v-model="form.commodity" list="commodities" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Rice" />
                    <datalist id="commodities">
                        <option v-for="c in catalog.commodities || []" :key="c" :value="c" />
                    </datalist>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Overall Budget</label>
                    <div class="relative mt-1 rounded-md shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <span class="text-slate-500 sm:text-sm">$</span>
                        </div>
                        <input v-model="form.overall_budget" type="number" min="0" step="0.01" 
                            class="block w-full rounded-lg border-slate-300 pl-7 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="0.00" />
                    </div>
                    <p v-if="form.errors.overall_budget" class="mt-1 text-xs text-red-600">{{ form.errors.overall_budget }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Start Date</label>
                    <input v-model="form.duration_start" type="date" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">End Date</label>
                    <input v-model="form.duration_end" type="date" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Funding Agency</label>
                    <input v-model="form.funding_agency" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g., DA-BAR" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Grant Code</label>
                    <input v-model="form.funding_code" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g., 2024-001" />
                </div>

                                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Research Objective</label>
                    <textarea v-model="form.objective" rows="4" 
                        class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                        placeholder="Describe the primary research goals, breeding targets, or experimental hypotheses..." />
                    <p v-if="form.errors.objective" class="mt-1 text-xs text-red-600">{{ form.errors.objective }}</p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50 rounded-b-xl">
            <button v-if="showCancelButton" type="button" @click="$emit('cancel')"
                class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Cancel
            </button>
            <button v-if="isEdit && showDeleteButton" type="button" :disabled="processing" @click="deleteProxy"
                class="inline-flex items-center rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                <LuTrash2 class="mr-2 h-4 w-4" />
                Delete
            </button>
            <button type="submit" :disabled="processing"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <LuLoader2 v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                <LuSave v-else class="mr-2 h-4 w-4" />
                {{ processing ? 'Saving...' : (isEdit ? 'Save Changes' : 'Create Project') }}
            </button>
        </div>
    </form>
</template>