<script>
import ResearchStudy from '@/Modules/domain/ResearchStudy'
import ResearchExperiment from '@/Modules/domain/ResearchExperiment'
import ResearchProjectForm from '@/Pages/Research/components/ResearchProjectForm.vue'

export default {
    name: 'ResearchProjectShow',
    components: { ResearchProjectForm },
    props: {
        project: { type: Object, required: true },
        catalog: { type: Object, default: () => ({}) },
        researchUsers: { type: Array, default: () => [] },
    },
    data() {
        return { editingProject: false }
    },
    computed: {
        ResearchStudy() { return ResearchStudy },
        ResearchExperiment() { return ResearchExperiment },
        permissions() { return this.$currentPermissions || [] },
        canUpdateProject() { return this.hasPermission('research.projects.update') },
        canDeleteProject() { return this.hasPermission('research.projects.delete') },
        canManageStudies() { return this.hasPermission('research.studies.manage') },
        canManageExperiments() { return this.hasPermission('research.experiments.manage') },
        studyTableParams() {
            return { project_id: this.project.id, per_page: 10, sort: 'updated_at', order: 'desc' }
        },
        experimentTableParams() {
            return { project_id: this.project.id, per_page: 10, sort: 'updated_at', order: 'desc' }
        },
    },
    methods: {
        hasPermission(permission) {
            return this.$isAdminUser || this.permissions.includes('*') || this.permissions.includes(permission)
        },
        formatCurrency(value) {
            if (!value) return '—'
            return Number(value).toLocaleString()
        },
        formatDateRange() {
            if (!this.project.duration_start && !this.project.duration_end) return 'Not scheduled'
            return [this.project.duration_start, this.project.duration_end].filter(Boolean).join(' – ')
        },
    },
}
</script>

<template>
    <AppLayout :title="project.title">
        <template #header>
            <div class="border-b border-slate-200 bg-white px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                            <Link :href="route('research.projects.index')" class="hover:text-slate-700">Projects</Link>
                            <LuChevronRight class="h-4 w-4" />
                            <span class="truncate">{{ project.code }}</span>
                        </div>
                        <h1 class="text-xl font-semibold text-slate-900 sm:text-2xl truncate">{{ project.title }}</h1>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <Link :href="route('research.samples.inventory')" 
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                            <LuBarcode class="h-4 w-4" />
                            Inventory
                        </Link>
                        <Link v-if="canManageStudies" :href="route('research.studies.create', project.id)" 
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                            <LuPlus class="h-4 w-4" />
                            Add Study
                        </Link>
                    </div>
                </div>
            </div>
        </template>

        <div class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8">
            <!-- Project Summary Card -->
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Project Overview</h2>
                        <p class="text-sm text-slate-500">Details and timeline</p>
                    </div>
                    <button v-if="canUpdateProject" @click="editingProject = !editingProject"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        <LuPencil class="h-4 w-4" />
                        {{ editingProject ? 'Cancel' : 'Edit' }}
                    </button>
                </div>

                <div class="p-6">
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Commodity</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ project.commodity || 'Not specified' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Budget</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ formatCurrency(project.overall_budget) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Duration</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ formatDateRange() }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Project Leader</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">
                                {{ project.project_leader?.name || 'Unassigned' }}
                                <span v-if="project.project_leader?.position" class="block text-xs font-normal text-slate-500">
                                    {{ project.project_leader.position }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Funding Agency</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ project.funding_agency || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Grant Code</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ project.funding_code || '—' }}</dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Objective</dt>
                            <dd class="mt-1 text-sm text-slate-700 leading-relaxed">
                                {{ project.objective || 'No objective recorded.' }}
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <ResearchProjectForm v-if="editingProject" :data="project" :catalog="catalog" 
                :research-users="researchUsers" :show-cancel-button="true" 
                :show-delete-button="canDeleteProject" @cancel="editingProject = false" 
                class="mt-6" />

            <!-- Studies Section -->
            <div class="mt-6 rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Studies</h2>
                        <p class="text-sm text-slate-500">{{ project.studies_count || 0 }} active studies</p>
                    </div>
                    <Link v-if="canManageStudies" :href="route('research.studies.create', project.id)" 
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        <LuPlus class="h-4 w-4" />
                        New Study
                    </Link>
                </div>
                <div class="p-6">
                    <CRCMDatatable :base-model="ResearchStudy" :params="studyTableParams"
                        :can-view="true" :can-create="false" 
                        :can-update="canManageStudies" :can-delete="canManageStudies" />
                </div>
            </div>

            <!-- Experiments Section -->
            <div class="mt-6 rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-900">Experiments</h2>
                    <p class="text-sm text-slate-500">All experiments across this project</p>
                </div>
                <div class="p-6">
                    <CRCMDatatable :base-model="ResearchExperiment" :params="experimentTableParams"
                        :can-view="true" :can-create="false" 
                        :can-update="canManageExperiments" :can-delete="canManageExperiments" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>