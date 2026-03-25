<script>
export default {
    name: 'ResearchDashboard',
    props: {
        stats: {
            type: Object,
            default: () => ({}),
        },
        recentProjects: {
            type: Array,
            default: () => [],
        },
        permissionMatrix: {
            type: Array,
            default: () => [],
        },
        sampleIdentifierExample: {
            type: String,
            default: '',
        },
        catalog: {
            type: Object,
            default: () => ({}),
        },
    },
    computed: {
        statCards() {
            return [
                { label: 'Projects', value: this.stats.projects || 0, icon: 'LuLayers' },
                { label: 'Studies', value: this.stats.studies || 0, icon: 'LuListChecks' },
                { label: 'Experiments', value: this.stats.experiments || 0, icon: 'LuFlaskConical' },
                { label: 'Tracked Samples', value: this.stats.samples || 0, icon: 'LuBarcode' },
            ]
        },
        commodityPreview() {
            return (this.catalog.commodities || []).slice(0, 8)
        },
    },
}
</script>

<template>
    <AppLayout title="Research Monitoring">
        <template #header>
            <ActionHeaderLayout
                title="Research Monitoring"
                subtitle="Project, study, experiment, and sample monitoring for rice and other commodities."
                :route-link="route('research.dashboard')"
            >
                <Link :href="route('research.projects.index')" class="rounded-lg bg-white/15 px-4 py-2 text-sm font-medium text-white hover:bg-white/25">
                    Open Projects
                </Link>
            </ActionHeaderLayout>
        </template>

        <div class="mx-auto max-w-7xl space-y-8 px-4 py-6">
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div v-for="card in statCards" :key="card.label" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-500">{{ card.label }}</p>
                        <component :is="card.icon" class="h-5 w-5 text-gray-400" />
                    </div>
                    <p class="mt-4 text-3xl font-semibold text-gray-900">{{ card.value }}</p>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Identifier Strategy</p>
                            <h2 class="mt-2 text-2xl font-semibold text-gray-900">Short barcode-safe IDs from field to lab to storage</h2>
                            <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600">
                                Each sample now gets a compact unique code built from the commodity prefix, the experiment sequence, the sample sequence, and a checksum.
                            </p>
                        </div>
                        <div class="rounded-2xl bg-gray-900 px-5 py-4 text-white shadow-inner">
                            <p class="text-xs uppercase tracking-[0.2em] text-gray-400">Example</p>
                            <p class="mt-2 font-mono text-2xl font-semibold">{{ sampleIdentifierExample }}</p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <div class="rounded-2xl bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Readable Context</p>
                            <p class="mt-2 text-sm text-gray-700">Keep long field references like PR code, line label, and plot numbers in dedicated fields without overloading the barcode.</p>
                        </div>
                        <div class="rounded-2xl bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Flexible Workflow</p>
                            <p class="mt-2 text-sm text-gray-700">The monitoring stages support germination, sowing, agro-morphology, post-harvest, and environmental logs.</p>
                        </div>
                        <div class="rounded-2xl bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Protected Data</p>
                            <p class="mt-2 text-sm text-gray-700">Sensitive objectives, personnel details, and monitoring payloads are encrypted at rest at the application layer.</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Commodity Scope</p>
                    <h2 class="mt-2 text-xl font-semibold text-gray-900">Generic by default</h2>
                    <p class="mt-3 text-sm leading-6 text-gray-600">
                        The module starts with rice-focused fields but accepts broader commodities and sample types for cross-program use.
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span v-for="commodity in commodityPreview" :key="commodity" class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                            {{ commodity }}
                        </span>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Recent Work</p>
                            <h2 class="mt-2 text-xl font-semibold text-gray-900">Active projects</h2>
                        </div>
                        <Link :href="route('research.projects.create')" class="rounded-lg border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            New Project
                        </Link>
                    </div>

                    <div class="mt-5 space-y-3">
                        <div v-if="!recentProjects.length" class="rounded-2xl border border-dashed border-gray-300 px-4 py-6 text-sm text-gray-500">
                            No research projects yet. Start with a project profile, then add studies and experiments.
                        </div>

                        <Link
                            v-for="project in recentProjects"
                            :key="project.id"
                            :href="route('research.projects.show', project.id)"
                            class="block rounded-2xl border border-gray-200 p-4 transition hover:border-emerald-300 hover:bg-emerald-50/40"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">{{ project.code }}</p>
                                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ project.title }}</p>
                                    <p class="mt-1 text-sm text-gray-600">{{ project.funding_agency || 'Funding agency not yet set' }}</p>
                                </div>
                                <div class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                    {{ project.studies_count }} studies
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">RBAC Matrix</p>
                    <h2 class="mt-2 text-xl font-semibold text-gray-900">Dedicated research roles</h2>
                    <div class="mt-4 space-y-4">
                        <div v-for="item in permissionMatrix" :key="item.role" class="rounded-2xl bg-gray-50 p-4">
                            <p class="text-sm font-semibold text-gray-900">{{ item.role }}</p>
                            <ul class="mt-3 space-y-2 text-sm text-gray-600">
                                <li v-for="permission in item.permissions" :key="permission" class="flex gap-2">
                                    <LuCheckCircle2 class="mt-0.5 h-4 w-4 flex-none text-emerald-600" />
                                    <span>{{ permission }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
