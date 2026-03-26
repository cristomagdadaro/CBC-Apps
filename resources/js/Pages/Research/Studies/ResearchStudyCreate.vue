<script>
import ResearchStudyForm from '@/Pages/Research/components/ResearchStudyForm.vue'

export default {
    name: 'ResearchStudyCreate',
    components: {
        ResearchStudyForm,
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
}
</script>

<template>
    <AppLayout title="Create Study">
        <template #header>
            <ActionHeaderLayout
                title="Create Study"
                :subtitle="`Add a study under ${project.title} before launching experiments.`"
                :route-link="route('research.projects.show', project.id)"
            >
                <Link :href="route('research.projects.show', project.id)" class="rounded-lg border border-white/25 px-4 py-2 text-sm font-medium text-white hover:bg-white/10">
                    Back to Project
                </Link>
            </ActionHeaderLayout>
        </template>

        <div class="mx-auto max-w-6xl space-y-6 px-4 py-6">
            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Parent Project</p>
                <div class="mt-3 grid gap-4 md:grid-cols-3">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Code</p>
                        <p class="mt-1 text-sm font-medium text-gray-900">{{ project.code }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Commodity</p>
                        <p class="mt-1 text-sm font-medium text-gray-900">{{ project.commodity || 'Not set' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Funding</p>
                        <p class="mt-1 text-sm font-medium text-gray-900">{{ project.funding_agency || 'Not set' }}</p>
                    </div>
                </div>
            </section>

            <ResearchStudyForm :project="project" :research-users="researchUsers" />
        </div>
    </AppLayout>
</template>