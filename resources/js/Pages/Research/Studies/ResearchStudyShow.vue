<script>
import ResearchStudy from "@/Modules/domain/ResearchStudy";
import ResearchExperiment from "@/Modules/domain/ResearchExperiment";
import ResearchStudyForm from "@/Pages/Research/components/ResearchStudyForm.vue";

export default {
  name: "ResearchStudyShow",
  components: {
    ResearchStudyForm,
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
    researchUsers: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      editingStudy: false,
    };
  },
  computed: {
    ResearchExperiment() {
      return ResearchExperiment;
    },
    permissions() {
      return this.$page.props.auth?.permissions || [];
    },
    canManageStudies() {
      return this.hasPermission("research.studies.manage");
    },
    canManageExperiments() {
      return this.hasPermission("research.experiments.manage");
    },
    studyTableParams() {
      return {
        study_id: this.study.id,
        per_page: 10,
        sort: "updated_at",
        order: "desc",
      };
    },
    staffMembers() {
      return Array.isArray(this.study.staff_members) ? this.study.staff_members : [];
    },
  },
  methods: {
    hasPermission(permission) {
      return this.permissions.includes("*") || this.permissions.includes(permission);
    },
    formatCurrency(value) {
      if (value === null || value === undefined || value === "") {
        return "Pending";
      }

      const amount = Number(value);
      return Number.isNaN(amount) ? value : amount.toLocaleString();
    },
  },
};
</script>

<template>
  <AppLayout :title="study.title">
    <template #header>
      <ActionHeaderLayout
        :title="study.title"
        subtitle="Review the study profile, then manage experiment records through dedicated pages and datatables."
        :route-link="route('research.studies.show', study.id)"
      >
        <Link
          :href="`${route('manuals.index')}?section=researchMonitoring`"
          class="rounded-lg border border-white/25 px-4 py-2 text-sm font-medium text-white hover:bg-white/10"
        >
          Manuals & Guides
        </Link>
        <Link
          :href="route('research.projects.show', study.project?.id)"
          class="rounded-lg border border-white/25 px-4 py-2 text-sm font-medium text-white hover:bg-white/10"
        >
          Back to Project
        </Link>
        <Link
          v-if="canManageExperiments"
          :href="route('research.experiments.create', study.id)"
          class="rounded-lg bg-white/15 px-4 py-2 text-sm font-medium text-white hover:bg-white/25"
        >
          New Experiment
        </Link>
      </ActionHeaderLayout>
    </template>

    <div class="space-y-6 px-4 py-6">
      <nav class="flex items-center gap-2 text-sm text-gray-500">
        <Link :href="route('research.dashboard')" class="hover:text-gray-700"
          >Dashboard</Link
        >
        <span>/</span>
        <Link :href="route('research.projects.index')" class="hover:text-gray-700"
          >Projects</Link
        >
        <span>/</span>
        <Link
          :href="route('research.projects.show', study.project?.id)"
          class="hover:text-gray-700"
          >{{ study.project?.code || "Project" }}</Link
        >
        <span>/</span>
        <span class="font-medium text-gray-700">{{ study.code }}</span>
      </nav>

      <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
              <p
                class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700"
              >
                {{ study.code }}
              </p>
              <h2 class="mt-2 text-2xl font-semibold text-gray-900">Study summary</h2>
              <p class="mt-2 text-sm text-gray-600">{{ study.project?.title }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
              <span
                class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700"
                >{{ study.experiments_count || 0 }} experiments</span
              >
            </div>
          </div>

          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div>
              <p class="text-xs uppercase tracking-wide text-gray-500">Study leader</p>
              <p class="mt-1 text-sm font-medium text-gray-900">
                {{ study.study_leader?.name || "Unassigned" }}
              </p>
              <p class="text-xs text-gray-500">
                {{ study.study_leader?.position || "Research role pending" }}
              </p>
            </div>
            <div>
              <p class="text-xs uppercase tracking-wide text-gray-500">Supervisor</p>
              <p class="mt-1 text-sm font-medium text-gray-900">
                {{ study.supervisor?.name || "Unassigned" }}
              </p>
              <p class="text-xs text-gray-500">
                {{ study.supervisor?.position || "Research role pending" }}
              </p>
            </div>
            <div>
              <p class="text-xs uppercase tracking-wide text-gray-500">Budget</p>
              <p class="mt-1 text-sm font-medium text-gray-900">
                {{ formatCurrency(study.budget) }}
              </p>
            </div>
            <div>
              <p class="text-xs uppercase tracking-wide text-gray-500">Project</p>
              <p class="mt-1 text-sm font-medium text-gray-900">
                {{ study.project?.title || "Project" }}
              </p>
            </div>
            <div class="md:col-span-2">
              <p class="text-xs uppercase tracking-wide text-gray-500">Objective</p>
              <p
                class="mt-1 rounded-2xl bg-gray-50 px-4 py-3 text-sm leading-6 text-gray-700"
              >
                {{ study.objective || "No study objective recorded yet." }}
              </p>
            </div>
            <div class="md:col-span-2">
              <p class="text-xs uppercase tracking-wide text-gray-500">Study staff</p>
              <div class="mt-2 flex flex-wrap gap-2">
                <span
                  v-if="!staffMembers.length"
                  class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600"
                  >No staff assigned</span
                >
                <span
                  v-for="member in staffMembers"
                  :key="member.id"
                  class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700"
                >
                  {{ member.name }}
                </span>
              </div>
            </div>
          </div>

          <div class="mt-6 flex flex-wrap justify-end gap-3">
            <button
              v-if="canManageStudies"
              type="button"
              class="rounded-xl border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
              @click="editingStudy = !editingStudy"
            >
              {{ editingStudy ? "Hide Update Form" : "Edit Study" }}
            </button>
          </div>
        </div>

        <div class="space-y-6">
          <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-widest text-emerald-700">
              Workflow
            </p>
            <h2 class="mt-2 text-2xl font-semibold text-gray-900">
              Study-first experiment planning
            </h2>
            <p class="mt-3 text-sm leading-6 text-gray-600">
              Keep this page focused on the study summary and experiment tracking. Create
              experiments on their own page so setup fields stay manageable.
            </p>
            <div class="mt-4 flex flex-wrap gap-3">
              <Link
                v-if="canManageExperiments"
                :href="route('research.experiments.create', study.id)"
                class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-black"
              >
                Create Experiment
              </Link>
              <Link
                :href="route('research.projects.show', study.project?.id)"
                class="rounded-lg border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                Open Project
              </Link>
            </div>
          </div>
        </div>
      </section>

      <ResearchStudyForm
        v-if="editingStudy"
        :data="study"
        :project="study.project"
        :research-users="researchUsers"
        :show-cancel-button="true"
        :show-delete-button="canManageStudies"
        @cancel="editingStudy = false"
      />

      <section
        class="space-y-4 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm"
      >
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">
              Experiments
            </p>
            <h2 class="mt-2 text-2xl font-semibold text-gray-900">
              Study experiment list
            </h2>
            <p class="mt-2 text-sm text-gray-600">
              Use the datatable to scan active experiments quickly instead of expanding
              cards one by one.
            </p>
          </div>
          <Link
            v-if="canManageExperiments"
            :href="route('research.experiments.create', study.id)"
            class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
          >
            New Experiment
          </Link>
        </div>

        <CRCMDatatable
          :base-model="ResearchExperiment"
          :params="studyTableParams"
          :can-view="true"
          :can-create="false"
          :can-update="canManageExperiments"
          :can-delete="canManageExperiments"
        />
      </section>
    </div>
  </AppLayout>
</template>
