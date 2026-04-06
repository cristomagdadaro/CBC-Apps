<script>
export default {
    name: "ResearchProjectIndex",
    props: {
        projects: { type: Array, default: () => [] },
        catalog: { type: Object, default: () => ({}) },
    },
    methods: {
        projectRouteIdentifier(project) {
            return project?.route_identifier || project?.funding_code || project?.code || project?.id
        },
    },
}
</script>

<template>
    <AppLayout title="Research Projects">
        <template #header>
            <ActionHeaderLayout
                title="Research Projects"
                subtitle="Manage projects, studies, and experimental workflows"
                :route-link="route('research.projects.index')"
                :breadcrumbs="headerBreadcrumbs"
            >
                <Link :href="`${route('manuals.index')}?section=researchMonitoring`" 
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                    <LuBookOpen class="h-4 w-4" />
                    Guides
                </Link>
                <Link :href="route('research.projects.create')" 
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                    <LuPlus class="h-4 w-4" />
                    New Project
                </Link>
            </ActionHeaderLayout>
        </template>

        <div class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8">
            <!-- Empty State -->
            <div v-if="!projects.length" class="flex flex-col items-center justify-center rounded-xl bg-white py-16 shadow-sm ring-1 ring-slate-200">
                <div class="rounded-full bg-slate-100 p-4">
                    <LuFolderKanban class="h-8 w-8 text-slate-400" />
                </div>
                <h3 class="mt-4 text-lg font-semibold text-slate-900">No projects found</h3>
                <p class="mt-1 text-sm text-slate-500">Create your first research project to begin tracking studies and experiments.</p>
                <Link :href="route('research.projects.create')" 
                    class="mt-6 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                    <LuPlus class="h-4 w-4" />
                    Create Project
                </Link>
            </div>

            <!-- Project Grid -->
            <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link v-for="project in projects" :key="project.id"
                    :href="route('research.projects.show', projectRouteIdentifier(project))"
                    class="group relative flex flex-col rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition-all hover:shadow-md hover:ring-indigo-200">
                    
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-xs font-mono text-slate-500">{{ project.code }}</p>
                            <h3 class="mt-1 line-clamp-2 text-base font-semibold text-slate-900 group-hover:text-indigo-600">
                                {{ project.title }}
                            </h3>
                        </div>
                        <span class="ml-2 inline-flex flex-none items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">
                            {{ project.studies_count }} studies
                        </span>
                    </div>

                    <div class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Commodity</span>
                            <span class="font-medium text-slate-900">{{ project.commodity || 'Mixed' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Budget</span>
                            <span class="font-medium text-slate-900">
                                {{ project.overall_budget ? Number(project.overall_budget).toLocaleString() : '—' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Funding</span>
                            <span class="truncate max-w-[150px] font-medium text-slate-900" :title="project.funding_agency">
                                {{ project.funding_agency || '—' }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-slate-100 pt-4">
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Recent Studies</p>
                        <div v-if="!(project.studies || []).length" class="mt-2 text-sm text-slate-400 italic">
                            No studies yet
                        </div>
                        <div v-for="study in (project.studies || []).slice(0, 2)" :key="study.id" 
                            class="mt-2 flex items-center justify-between text-sm">
                            <span class="truncate text-slate-700">{{ study.title }}</span>
                            <span class="ml-2 flex-none text-xs text-slate-500">{{ study.experiments_count }} exp</span>
                        </div>
                    </div>

                    <div class="absolute inset-x-0 bottom-0 h-1 rounded-b-xl bg-indigo-600 opacity-0 transition-opacity group-hover:opacity-100"></div>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
