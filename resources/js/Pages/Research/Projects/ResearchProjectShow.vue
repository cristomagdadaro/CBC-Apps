<script>
import ResearchStudy from '@/Modules/domain/ResearchStudy'
import ResearchExperiment from '@/Modules/domain/ResearchExperiment'
import ResearchProjectForm from '@/Pages/Research/components/ResearchProjectForm.vue'

export default {
    name: 'ResearchProjectShow',
    components: {
        ResearchProjectForm,
    },
    props: {
        project: {
            type: Object,
            required: true,
        },
        catalog: {
            type: Object,
            default: () => ({}),
        },
        researchUsers: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            editingProject: false,
        }
    },
    computed: {
        ResearchStudy() {
            return ResearchStudy
        },
        ResearchExperiment() {
            return ResearchExperiment
        },
        permissions() {
            return this.$page.props.auth?.permissions || []
        },
        canUpdateProject() {
            return this.hasPermission('research.projects.update')
        },
        canDeleteProject() {
            return this.hasPermission('research.projects.delete')
        },
        canManageStudies() {
            return this.hasPermission('research.studies.manage')
        },
        canManageExperiments() {
            return this.hasPermission('research.experiments.manage')
        },
        studyTableParams() {
            return {
                project_id: this.project.id,
                per_page: 10,
                sort: 'updated_at',
                order: 'desc',
            }
        },
        experimentTableParams() {
            return {
                project_id: this.project.id,
                per_page: 10,
                sort: 'updated_at',
                order: 'desc',
            }
        },
    },
    methods: {
        hasPermission(permission) {
            return this.permissions.includes('*') || this.permissions.includes(permission)
        },
        formatCurrency(value) {
            if (value === null || value === undefined || value === '') {
                return 'Pending'
            }

            const amount = Number(value)
            return Number.isNaN(amount) ? value : amount.toLocaleString()
        },
        formatDateRange() {
            if (!this.project.duration_start && !this.project.duration_end) {
                return 'Schedule not set'
            }

            return [this.project.duration_start, this.project.duration_end].filter(Boolean).join(' to ')
        },
    },
}
</script>

<template>
    <AppLayout :title="project.title">
        <template #header>
            <ActionHeaderLayout
                :title="project.title"
                subtitle="Manage the project profile, create studies, and launch experiment monitoring."
                :route-link="route('research.projects.show', project.id)"
            >
                <Link :href="`${route('manuals.index')}?section=researchMonitoring`" class="rounded-lg border border-white/25 px-4 py-2 text-sm font-medium text-white hover:bg-white/10">
                    Manuals & Guides
                </Link>
                <Link :href="route('research.samples.inventory')" class="rounded-lg border border-white/25 px-4 py-2 text-sm font-medium text-white hover:bg-white/10">
                    Sample Inventory
                </Link>
                <Link :href="route('research.projects.index')" class="rounded-lg border border-white/25 px-4 py-2 text-sm font-medium text-white hover:bg-white/10">
                    All Projects
                </Link>
                <Link :href="route('research.dashboard')" class="rounded-lg bg-white/15 px-4 py-2 text-sm font-medium text-white hover:bg-white/25">
                    Dashboard
                </Link>
            </ActionHeaderLayout>
        </template>

        <div class="space-y-6 px-4 py-6">
            <nav class="flex items-center gap-2 text-sm text-gray-500">
                <Link :href="route('research.dashboard')" class="hover:text-gray-700">Dashboard</Link>
                <span>/</span>
                <Link :href="route('research.projects.index')" class="hover:text-gray-700">Projects</Link>
                <span>/</span>
                <span class="font-medium text-gray-700">{{ project.code }}</span>
            </nav>

            <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">{{ project.code }}</p>
                            <h2 class="mt-2 text-2xl font-semibold text-gray-900">Project summary</h2>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">{{ project.studies_count || 0 }} studies</span>
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">{{ project.experiments_count || 0 }} experiments</span>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500">Commodity</p>
                            <p class="mt-1 text-sm font-medium text-gray-900">{{ project.commodity || 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500">Budget</p>
                            <p class="mt-1 text-sm font-medium text-gray-900">{{ formatCurrency(project.overall_budget) }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500">Schedule</p>
                            <p class="mt-1 text-sm font-medium text-gray-900">{{ formatDateRange() }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500">Project leader</p>
                            <p class="mt-1 text-sm font-medium text-gray-900">{{ project.project_leader?.name || 'Unassigned' }}</p>
                            <p class="text-xs text-gray-500">{{ project.project_leader?.position || 'Research role pending' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500">Funding agency</p>
                            <p class="mt-1 text-sm font-medium text-gray-900">{{ project.funding_agency || 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500">Funding code</p>
                            <p class="mt-1 text-sm font-medium text-gray-900">{{ project.funding_code || 'Not set' }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Objective</p>
                            <p class="mt-1 rounded-2xl bg-gray-50 px-4 py-3 text-sm leading-6 text-gray-700">{{ project.objective || 'No project objective recorded yet.' }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap justify-end gap-3">
                        <button v-if="canUpdateProject" type="button" class="rounded-xl border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="editingProject = !editingProject">
                            {{ editingProject ? 'Hide Update Form' : 'Edit Project' }}
                        </button>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-widest text-emerald-700">Guides</p>
                        <h2 class="mt-2 text-2xl font-semibold text-gray-900">Need full workflow instructions?</h2>
                        <p class="mt-2 text-sm leading-6 text-gray-600">Detailed process notes were moved to Manuals & Guides to keep this workspace focused on the current record summary and datatable review.</p>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <Link :href="`${route('manuals.index')}?section=researchMonitoring`" class="rounded-lg border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Open Research Guide
                            </Link>
                            <Link :href="route('research.samples.inventory')" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">
                                Open Sample Inventory
                            </Link>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-widest text-emerald-700">Next Step</p>
                        <h2 class="mt-2 text-2xl font-semibold text-gray-900">Create studies on their own page</h2>
                        <p class="mt-3 text-sm leading-6 text-gray-600">Keep study creation separate from the project summary so this page stays readable even when a project grows.</p>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <Link v-if="canManageStudies" :href="route('research.studies.create', project.id)" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                                New Study
                            </Link>
                            <span v-else class="rounded-lg border px-4 py-2 text-sm font-medium text-gray-500">Study creation requires study management permission.</span>
                        </div>
                    </div>
                </div>
            </section>

            <ResearchProjectForm
                v-if="editingProject"
                :data="project"
                :catalog="catalog"
                :research-users="researchUsers"
                :show-cancel-button="true"
                :show-delete-button="canDeleteProject"
                @cancel="editingProject = false"
            />

            <section class="space-y-4 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Studies</p>
                        <h2 class="mt-2 text-2xl font-semibold text-gray-900">Project study list</h2>
                        <p class="mt-2 text-sm text-gray-600">Use the datatable to scan study ownership, budget, and experiment counts without expanding large cards.</p>
                    </div>
                    <Link v-if="canManageStudies" :href="route('research.studies.create', project.id)" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                        New Study
                    </Link>
                </div>

                <CRCMDatatable
                    :base-model="ResearchStudy"
                    :params="studyTableParams"
                    :can-view="true"
                    :can-create="false"
                    :can-update="canManageStudies"
                    :can-delete="canManageStudies"
                />
            </section>

            <section class="space-y-4 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Experiments</p>
                        <h2 class="mt-2 text-2xl font-semibold text-gray-900">Project experiment list</h2>
                        <p class="mt-2 text-sm text-gray-600">All experiments under this project are listed here so you can compare locations, season tags, and sample counts in one view.</p>
                    </div>
                    <span class="rounded-lg border px-4 py-2 text-sm font-medium text-gray-500">Create experiments from a study page.</span>
                </div>

                <CRCMDatatable
                    :base-model="ResearchExperiment"
                    :params="experimentTableParams"
                    :can-view="true"
                    :can-create="false"
                    :can-update="canManageExperiments"
                    :can-delete="canManageExperiments"
                />
            </section>
        </div>
    </AppLayout>
</template>
