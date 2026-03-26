<script>
import ResearchExperimentForm from "@/Pages/Research/components/ResearchExperimentForm.vue";

export default {
  name: "ResearchExperimentCreate",
  components: {
    ResearchExperimentForm,
  },
  props: {
    study: {
      type: Object,
      required: true,
    },
    catalog: {
      type: Object,
      default: () => ({}),
    },
  },
};
</script>

<template>
  <AppLayout title="Create Experiment">
    <template #header>
      <ActionHeaderLayout
        title="Create Experiment"
        :subtitle="`Add an experiment under ${study.title} before recording samples and monitoring records.`"
        :route-link="route('research.studies.show', study.id)"
      >
        <Link
          :href="route('research.studies.show', study.id)"
          class="rounded-lg border border-white/25 px-4 py-2 text-sm font-medium text-white hover:bg-white/10"
        >
          Back to Study
        </Link>
      </ActionHeaderLayout>
    </template>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6">
      <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">
          Parent Study
        </p>
        <div class="mt-3 grid gap-4 md:grid-cols-3">
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-500">Study</p>
            <p class="mt-1 text-sm font-medium text-gray-900">{{ study.title }}</p>
          </div>
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-500">Project</p>
            <p class="mt-1 text-sm font-medium text-gray-900">
              {{ study.project?.title || "Project" }}
            </p>
          </div>
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-500">Commodity</p>
            <p class="mt-1 text-sm font-medium text-gray-900">
              {{ study.project?.commodity || "Rice" }}
            </p>
          </div>
        </div>
      </section>

      <ResearchExperimentForm :study="study" :catalog="catalog" />
    </div>
  </AppLayout>
</template>
