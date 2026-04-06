<script>
import ResearchStudy from "@/Modules/domain/ResearchStudy";
import ResearchExperiment from "@/Modules/domain/ResearchExperiment";
import ResearchStudyForm from "@/Pages/Research/components/ResearchStudyForm.vue";

export default {
    name: "ResearchStudyShow",
    components: { ResearchStudyForm },
    props: {
        study: { type: Object, required: true },
        catalog: { type: Object, default: () => ({}) },
        researchUsers: { type: Array, default: () => [] },
    },
    data() {
        return { editingStudy: false }
    },
    computed: {
        ResearchExperiment() { return ResearchExperiment },
        permissions() { return this.$currentPermissions || [] },
        canManageStudies() { return this.hasPermission("research.studies.manage") },
        canManageExperiments() { return this.hasPermission("research.experiments.manage") },
        studyTableParams() {
            return { study_id: this.study.id, per_page: 10, sort: "updated_at", order: "desc" }
        },
        staffMembers() {
            return Array.isArray(this.study.staff_members) ? this.study.staff_members : []
        },
    },
    methods: {
        hasPermission(permission) {
            return this.$isAdminUser || this.permissions.includes("*") || this.permissions.includes(permission)
        },
        formatCurrency(value) {
            if (!value) return "—"
            return Number(value).toLocaleString()
        },
    },
}
</script>

<template>
    <AppLayout :title="study.title">
        <template #header>
            <div class="border-b border-slate-200 bg-white px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                            <Link :href="route('research.projects.index')" class="hover:text-slate-700">Projects</Link>
                            <LuChevronRight class="h-4 w-4" />
                            <Link :href="route('research.projects.show', study.project?.id)" class="hover:text-slate-700 truncate max-w-[150px]">
                                {{ study.project?.code || "Project" }}
                            </Link>
                            <LuChevronRight class="h-4 w-4" />
                            <span class="text-slate-900">{{ study.code }}</span>
                        </div>
                        <h1 class="text-xl font-semibold text-slate-900 sm:text-2xl truncate">{{ study.title }}</h1>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <Link :href="route('research.projects.show', study.project?.id)"
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                            <LuArrowLeft class="h-4 w-4" />
                            Project
                        </Link>
                        <Link v-if="canManageExperiments" :href="route('research.experiments.create', study.id)"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                            <LuPlus class="h-4 w-4" />
                            Add Experiment
                        </Link>
                    </div>
                </div>
            </div>
        </template>

        <div class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8">
            <!-- Study Overview -->
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-mono text-slate-500">{{ study.code }}</p>
                        <h2 class="text-lg font-semibold text-slate-900">Study Overview</h2>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                            {{ study.experiments_count || 0 }} experiments
                        </span>
                        <button v-if="canManageStudies" @click="editingStudy = !editingStudy"
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            <LuPencil class="h-4 w-4" />
                            {{ editingStudy ? 'Cancel' : 'Edit' }}
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Study Leader</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">
                                {{ study.study_leader?.name || "Unassigned" }}
                                <span v-if="study.study_leader?.position" class="block text-xs font-normal text-slate-500">
                                    {{ study.study_leader.position }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Supervisor</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">
                                {{ study.supervisor?.name || "Unassigned" }}
                                <span v-if="study.supervisor?.position" class="block text-xs font-normal text-slate-500">
                                    {{ study.supervisor.position }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Budget</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ formatCurrency(study.budget) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Project</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">
                                <Link :href="route('research.projects.show', study.project?.id)" class="text-indigo-600 hover:text-indigo-700">
                                    {{ study.project?.title || "—" }}
                                </Link>
                            </dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Objective</dt>
                            <dd class="mt-1 text-sm text-slate-700 leading-relaxed">
                                {{ study.objective || "No objective recorded." }}
                            </dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Study Staff</dt>
                            <dd class="mt-1 flex flex-wrap gap-2">
                                <span v-if="!staffMembers.length" class="text-sm text-slate-400 italic">No staff assigned</span>
                                <span v-for="member in staffMembers" :key="member.id"
                                    class="inline-flex items-center rounded-md bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                                    {{ member.name }}
                                </span>
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <ResearchStudyForm v-if="editingStudy" :data="study" :project="study.project"
                :research-users="researchUsers" :show-cancel-button="true" :show-delete-button="canManageStudies"
                @cancel="editingStudy = false" class="mt-6" />

            <!-- Experiments Section -->
            <div class="mt-6 rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Experiments</h2>
                        <p class="text-sm text-slate-500">Manage experimental protocols and sample tracking</p>
                    </div>
                    <Link v-if="canManageExperiments" :href="route('research.experiments.create', study.id)"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        <LuPlus class="h-4 w-4" />
                        New Experiment
                    </Link>
                </div>
                <div class="p-6">
                    <CRCMDatatable :base-model="ResearchExperiment" :params="studyTableParams"
                        :can-view="true" :can-create="false" :can-update="canManageExperiments" :can-delete="canManageExperiments" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>