<script>
export default {
    name: 'ResearchProjectIndex',
    props: {
        projects: {
            type: Array,
            default: () => [],
        },
        catalog: {
            type: Object,
            default: () => ({}),
        },
    },
}
</script>

<template>
    <AppLayout title="Research Projects">
        <template #header>
            <ActionHeaderLayout
                title="Research Projects"
                subtitle="Create research portfolios, attach studies, and launch experiment monitoring."
                :route-link="route('research.projects.index')"
            >
                <Link :href="route('research.dashboard')" class="rounded-lg border border-white/25 px-4 py-2 text-sm font-medium text-white hover:bg-white/10">
                    Dashboard
                </Link>
                <Link :href="route('research.projects.create')" class="rounded-lg bg-white/15 px-4 py-2 text-sm font-medium text-white hover:bg-white/25">
                    New Project
                </Link>
            </ActionHeaderLayout>
        </template>

        <div class="mx-auto max-w-7xl space-y-6 px-4 py-6">
            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Module Scope</p>
                        <h2 class="mt-2 text-2xl font-semibold text-gray-900">Rice-first, commodity-flexible research tracking</h2>
                        <p class="mt-3 max-w-3xl text-sm leading-6 text-gray-600">
                            Use a consistent profile structure for projects, studies, experiments, field samples, lab handoffs, storage, and monitoring records.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="commodity in (catalog.commodities || []).slice(0, 10)" :key="commodity" class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                            {{ commodity }}
                        </span>
                    </div>
                </div>
            </section>

            <section class="grid gap-5 lg:grid-cols-2 xl:grid-cols-3">
                <div v-if="!projects.length" class="rounded-3xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500 lg:col-span-2 xl:col-span-3">
                    No projects yet. Start by creating the research project profile, then build out studies and experiments.
                </div>

                <Link
                    v-for="project in projects"
                    :key="project.id"
                    :href="route('research.projects.show', project.id)"
                    class="group rounded-3xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">{{ project.code }}</p>
                            <h2 class="mt-2 text-xl font-semibold text-gray-900 group-hover:text-emerald-700">{{ project.title }}</h2>
                        </div>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                            {{ project.studies_count }} studies
                        </span>
                    </div>

                    <div class="mt-5 space-y-2 text-sm text-gray-600">
                        <p><span class="font-medium text-gray-900">Commodity:</span> {{ project.commodity || 'Generic / mixed' }}</p>
                        <p><span class="font-medium text-gray-900">Funding:</span> {{ project.funding_agency || 'Not yet set' }}</p>
                        <p><span class="font-medium text-gray-900">Budget:</span> {{ project.overall_budget ? Number(project.overall_budget).toLocaleString() : 'Pending' }}</p>
                    </div>

                    <div class="mt-5 border-t border-gray-100 pt-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Study Snapshot</p>
                        <div v-if="!(project.studies || []).length" class="mt-3 rounded-2xl bg-gray-50 px-4 py-3 text-sm text-gray-500">
                            Add the first study inside this project.
                        </div>
                        <div v-for="study in (project.studies || []).slice(0, 3)" :key="study.id" class="mt-3 rounded-2xl bg-gray-50 px-4 py-3">
                            <p class="text-sm font-medium text-gray-900">{{ study.title }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ study.experiments_count }} experiments</p>
                        </div>
                    </div>
                </Link>
            </section>
        </div>
    </AppLayout>
</template>
