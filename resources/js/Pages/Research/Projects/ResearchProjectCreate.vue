<script>
import ResearchProjectForm from '@/Pages/Research/components/ResearchProjectForm.vue'

export default {
    name: 'ResearchProjectCreate',
    components: {
        ResearchProjectForm,
    },
    props: {
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
    <AppLayout title="Create Research Project">
        <template #header>
            <ActionHeaderLayout
                title="Create Research Project"
                subtitle="Start the top-level profile before adding studies, experiments, and sample flows."
                :route-link="route('research.projects.index')"
            />
        </template>

        <div class="mx-auto max-w-6xl space-y-6 px-4 py-6">
            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm text-sm leading-6 text-gray-600">
                Project creation stays separate from the project dashboard so the list and summary pages remain easy to scan. After saving this profile, add studies and experiments from their own dedicated pages.
            </section>

            <ResearchProjectForm :catalog="catalog" :research-users="researchUsers" />
        </div>
    </AppLayout>
</template>
