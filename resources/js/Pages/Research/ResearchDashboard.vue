<script>
export default {
    name: 'ResearchDashboard',
    props: {
        stats: { type: Object, default: () => ({}) },
        recentProjects: { type: Array, default: () => [] },
        permissionMatrix: { type: Array, default: () => [] },
        sampleIdentifierExample: { type: String, default: '' },
        catalog: { type: Object, default: () => ({}) },
    },
    computed: {
        statCards() {
            return [
                { label: 'Active Projects', value: this.stats.projects || 0, icon: 'LuFolderKanban', color: 'text-blue-600', bg: 'bg-blue-50' },
                { label: 'Ongoing Studies', value: this.stats.studies || 0, icon: 'LuClipboardList', color: 'text-indigo-600', bg: 'bg-indigo-50' },
                { label: 'Experiments', value: this.stats.experiments || 0, icon: 'LuFlaskConical', color: 'text-amber-600', bg: 'bg-amber-50' },
                { label: 'Tracked Samples', value: this.stats.samples || 0, icon: 'LuDna', color: 'text-emerald-600', bg: 'bg-emerald-50' },
            ]
        },
        commodityPreview() {
            return (this.catalog.commodities || []).slice(0, 6)
        },
    },
    methods: {
        projectRouteIdentifier(project) {
            return project?.route_identifier || project?.funding_code || project?.code || project?.id
        },
    },
}
</script>

<template>
    <AppLayout title="Research Portal">
        <template #header>
            <ActionHeaderLayout
                title="Research Portal"
                subtitle="Project management, sample tracking, and experiment monitoring"
                route-link="research.dashboard"
            >
                <Link :href="`${route('manuals.index')}?section=researchMonitoring`"
                    class="inline-flex items-center gap-2 rounded-lg border border-white/25 px-4 py-2 text-sm font-medium text-white hover:bg-white/10">
                    <LuBookOpen class="h-4 w-4" />
                    Manuals & Guides
                </Link>
                <Link :href="route('research.samples.inventory')"
                    class="inline-flex items-center gap-2 rounded-lg border border-white/25 px-4 py-2 text-sm font-medium text-white hover:bg-white/10">
                    <LuBarcode class="h-4 w-4" />
                    Sample Inventory
                </Link>
                <Link :href="route('research.projects.index')"
                    class="inline-flex items-center gap-2 rounded-lg bg-white/15 px-4 py-2 text-sm font-medium text-white hover:bg-white/25">
                    <LuArrowRight class="h-4 w-4" />
                    Open Projects
                </Link>
            </ActionHeaderLayout>
        </template>

        <div class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8">
            <!-- Stats Grid -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="card in statCards" :key="card.label" 
                    class="relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">{{ card.label }}</p>
                            <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ card.value }}</p>
                        </div>
                        <div :class="[card.bg, 'rounded-lg p-3']">
                            <component :is="card.icon" :class="[card.color, 'h-6 w-6']" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                <!-- Sample Inventory Card -->
                <div class="lg:col-span-2 rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <div class="flex items-center gap-2">
                            <LuScanLine class="h-5 w-5 text-indigo-600" />
                            <h2 class="text-lg font-semibold text-slate-900">Sample Tracking</h2>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">Barcode workflows and QR retrieval systems</p>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-lg bg-slate-50 p-4 ring-1 ring-slate-200">
                                <div class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                    <LuBarcode class="h-4 w-4 text-slate-400" />
                                    Barcode Standard
                                </div>
                                <code class="mt-2 block text-lg font-semibold text-slate-900">{{ sampleIdentifierExample || 'RS-2024-0001' }}</code>
                                <p class="mt-1 text-xs text-slate-500">Use for shelf verification and audits</p>
                            </div>
                            
                            <div class="rounded-lg bg-indigo-50 p-4 ring-1 ring-indigo-100">
                                <div class="flex items-center gap-2 text-sm font-medium text-indigo-900">
                                    <LuQrCode class="h-4 w-4 text-indigo-600" />
                                    QR Retrieval
                                </div>
                                <p class="mt-2 text-sm text-indigo-700">Scan QR codes for instant sample details and history</p>
                                <Link :href="route('research.samples.inventory')" 
                                    class="mt-3 inline-flex items-center text-sm font-medium text-indigo-700 hover:text-indigo-800">
                                    Open Inventory →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commodities -->
                <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-lg font-semibold text-slate-900">Research Scope</h2>
                        <p class="mt-1 text-sm text-slate-500">Supported commodities and sample types</p>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-wrap gap-2">
                            <span v-for="commodity in commodityPreview" :key="commodity" 
                                class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 ring-1 ring-inset ring-slate-200">
                                {{ commodity }}
                            </span>
                            <span v-if="(catalog.commodities || []).length > 6" 
                                class="inline-flex items-center rounded-md bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-500">
                                +{{ (catalog.commodities || []).length - 6 }} more
                            </span>
                        </div>
                        <div class="mt-4 rounded-lg bg-amber-50 p-3 ring-1 ring-amber-100">
                            <p class="text-xs text-amber-800">
                                <span class="font-semibold">Note:</span> Rice-optimized fields with cross-commodity support for broader research programs.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Projects & Permissions -->
            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Recent Projects</h2>
                            <p class="mt-1 text-sm text-slate-500">Active research portfolios</p>
                        </div>
                        <Link :href="route('research.projects.create')" 
                            class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">
                            <LuPlus class="h-4 w-4" />
                            New
                        </Link>
                    </div>
                    
                    <div class="divide-y divide-slate-100">
                        <div v-if="!recentProjects.length" class="px-6 py-12 text-center">
                            <LuFolderOpen class="mx-auto h-10 w-10 text-slate-300" />
                            <h3 class="mt-3 text-sm font-medium text-slate-900">No projects yet</h3>
                            <p class="mt-1 text-sm text-slate-500">Get started by creating a research project.</p>
                            <Link :href="route('research.projects.create')" 
                                class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                Create Project
                            </Link>
                        </div>

                        <Link v-for="project in recentProjects" :key="project.id"
                            :href="route('research.projects.show', projectRouteIdentifier(project))"
                            class="group flex items-start justify-between px-6 py-4 hover:bg-slate-50 transition-colors">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-mono text-slate-500">{{ project.code }}</span>
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                                        {{ project.studies_count }} studies
                                    </span>
                                </div>
                                <h3 class="mt-1 truncate text-sm font-medium text-slate-900 group-hover:text-indigo-600">
                                    {{ project.title }}
                                </h3>
                                <p class="mt-1 text-sm text-slate-500">{{ project.funding_agency || 'No funding agency' }}</p>
                            </div>
                            <LuChevronRight class="h-5 w-5 text-slate-400 group-hover:text-indigo-600" />
                        </Link>
                    </div>
                </div>

                <!-- RBAC -->
                <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-lg font-semibold text-slate-900">Access Control</h2>
                        <p class="mt-1 text-sm text-slate-500">Role-based permissions</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <div v-for="item in permissionMatrix" :key="item.role" class="space-y-2">
                            <h3 class="text-sm font-semibold text-slate-900">{{ item.role }}</h3>
                            <ul class="space-y-1.5">
                                <li v-for="permission in item.permissions" :key="permission" 
                                    class="flex items-start gap-2 text-sm text-slate-600">
                                    <LuCheck class="h-4 w-4 flex-none text-emerald-500 mt-0.5" />
                                    <span>{{ permission }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
