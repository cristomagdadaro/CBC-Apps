<script>
import axios from "axios";
import { router } from "@inertiajs/vue3";
import KeyValueEditor from "@/Pages/Research/components/KeyValueEditor.vue";
import ResearchExperimentForm from "@/Pages/Research/components/ResearchExperimentForm.vue";

export default {
  name: "ResearchExperimentShow",
  components: {
    KeyValueEditor,
    ResearchExperimentForm,
  },
  props: {
    experiment: {
      type: Object,
      required: true,
    },
    catalog: {
      type: Object,
      default: () => ({}),
    },
  },
  data() {
    return {
      newSampleForm: this.defaultSampleForm(this.experiment.id),
      sampleForms: this.buildSampleForms(this.experiment.samples || []),
      recordForms: this.buildRecordForms(this.experiment.samples || []),
      sampleCreateErrors: {},
      sampleErrors: {},
      recordErrors: {},
      creatingSample: false,
      savingSamples: {},
      deletingSamples: {},
      creatingRecords: {},
      deletingRecords: {},
      editingExperiment: false,
    };
  },
  computed: {
    permissions() {
      return this.$currentPermissions || [];
    },
    canExport() {
      return (
        this.$isAdminUser ||
        this.permissions.includes("*") ||
        this.permissions.includes("research.exports.manage")
      );
    },
    canManageExperiment() {
      return (
        this.$isAdminUser ||
        this.permissions.includes("*") ||
        this.permissions.includes("research.experiments.manage")
      );
    },
    canManageSamples() {
      return (
        this.$isAdminUser ||
        this.permissions.includes("*") ||
        this.permissions.includes("research.samples.manage")
      );
    },
    canManageMonitoring() {
      return (
        this.$isAdminUser ||
        this.permissions.includes("*") ||
        this.permissions.includes("research.monitoring.manage")
      );
    },
  },
  methods: {
    defaultSampleForm(experimentId) {
      return {
        experiment_id: experimentId,
        commodity: this.experiment?.commodity || "Rice",
        sample_type: this.experiment?.sample_type || "Seeds",
        accession_name: "",
        pr_code: "",
        field_label: "",
        line_label: "",
        plant_label: "",
        generation: this.experiment?.generation || "",
        plot_number: this.experiment?.plot_number || "",
        field_number: this.experiment?.field_number || "",
        replication_number: this.experiment?.replication_number || "",
        current_status: "Field",
        current_location: "",
        storage_location: "",
        germination_date: "",
        sowing_date: "",
        harvest_date: "",
        is_priority: false,
        legacy_reference: "",
      };
    },
    buildSampleForms(samples) {
      return samples.reduce((forms, sample) => {
        forms[sample.id] = {
          commodity: sample.commodity || "",
          sample_type: sample.sample_type || "",
          accession_name: sample.accession_name || "",
          pr_code: sample.pr_code || "",
          field_label: sample.field_label || "",
          line_label: sample.line_label || "",
          plant_label: sample.plant_label || "",
          generation: sample.generation || "",
          plot_number: sample.plot_number || "",
          field_number: sample.field_number || "",
          replication_number: sample.replication_number || "",
          current_status: sample.current_status || "",
          current_location: sample.current_location || "",
          storage_location: sample.storage_location || "",
          germination_date: sample.germination_date || "",
          sowing_date: sample.sowing_date || "",
          harvest_date: sample.harvest_date || "",
          is_priority: !!sample.is_priority,
          legacy_reference: sample.legacy_reference || "",
        };
        return forms;
      }, {});
    },
    buildRecordForms(samples) {
      return samples.reduce((forms, sample) => {
        forms[sample.id] = this.defaultRecordForm(sample.id);
        return forms;
      }, {});
    },
    defaultRecordForm(sampleId) {
      return {
        sample_id: sampleId,
        stage: "germination",
        recorded_on: new Date().toISOString().slice(0, 10),
        parameter_entries: this.stageSuggestions("germination"),
        notes: "",
        selected_for_export: false,
      };
    },
    stageSuggestions(stage) {
      return ((this.catalog.stages || {})[stage]?.suggested_parameters || []).map(
        (parameter) => ({
          key: parameter,
          value: "",
        })
      );
    },
    stageLabel(stage) {
      return (this.catalog.stages || {})[stage]?.label || stage;
    },
    sanitizeSamplePayload(form) {
      return {
        ...form,
        replication_number: form.replication_number || null,
      };
    },
    sanitizeRecordPayload(form) {
      const parameterSet = (form.parameter_entries || []).reduce((payload, row) => {
        if (row?.key) {
          payload[row.key] = row?.value || "";
        }
        return payload;
      }, {});

      return {
        sample_id: form.sample_id,
        stage: form.stage,
        recorded_on: form.recorded_on,
        parameter_set: parameterSet,
        notes: form.notes,
        selected_for_export: !!form.selected_for_export,
      };
    },
    fieldError(errors, field) {
      return errors?.[field]?.[0] || "";
    },
    resetRecordForm(sampleId, stage = "germination") {
      this.recordForms = {
        ...this.recordForms,
        [sampleId]: {
          sample_id: sampleId,
          stage,
          recorded_on: new Date().toISOString().slice(0, 10),
          parameter_entries: this.stageSuggestions(stage),
          notes: "",
          selected_for_export: false,
        },
      };
    },
    handleRecordStageChange(sampleId) {
      const stage = this.recordForms[sampleId].stage;
      this.recordForms = {
        ...this.recordForms,
        [sampleId]: {
          ...this.recordForms[sampleId],
          parameter_entries: this.stageSuggestions(stage),
        },
      };
    },
    async createSample() {
      this.creatingSample = true;
      this.sampleCreateErrors = {};

      try {
        await axios.post(
          route("api.research.samples.store"),
          this.sanitizeSamplePayload(this.newSampleForm)
        );
        router.visit(route("research.experiments.show", this.experiment.id));
      } catch (error) {
        this.sampleCreateErrors = error?.response?.data?.errors || {};
      } finally {
        this.creatingSample = false;
      }
    },
    async saveSample(sampleId) {
      this.savingSamples = { ...this.savingSamples, [sampleId]: true };
      this.sampleErrors = { ...this.sampleErrors, [sampleId]: {} };

      try {
        await axios.put(
          route("api.research.samples.update", sampleId),
          this.sanitizeSamplePayload(this.sampleForms[sampleId])
        );
        router.visit(route("research.experiments.show", this.experiment.id));
      } catch (error) {
        this.sampleErrors = {
          ...this.sampleErrors,
          [sampleId]: error?.response?.data?.errors || {},
        };
      } finally {
        this.savingSamples = { ...this.savingSamples, [sampleId]: false };
      }
    },
    async deleteSample(sampleId) {
      if (!confirm("Delete this sample and its monitoring records?")) {
        return;
      }

      this.deletingSamples = { ...this.deletingSamples, [sampleId]: true };

      try {
        await axios.delete(route("api.research.samples.destroy", sampleId));
        router.visit(route("research.experiments.show", this.experiment.id));
      } finally {
        this.deletingSamples = { ...this.deletingSamples, [sampleId]: false };
      }
    },
    async createRecord(sampleId) {
      this.creatingRecords = { ...this.creatingRecords, [sampleId]: true };
      this.recordErrors = { ...this.recordErrors, [sampleId]: {} };

      try {
        await axios.post(
          route("api.research.records.store"),
          this.sanitizeRecordPayload(this.recordForms[sampleId])
        );
        router.visit(route("research.experiments.show", this.experiment.id));
      } catch (error) {
        this.recordErrors = {
          ...this.recordErrors,
          [sampleId]: error?.response?.data?.errors || {},
        };
      } finally {
        this.creatingRecords = { ...this.creatingRecords, [sampleId]: false };
      }
    },
    async deleteRecord(recordId, sampleId) {
      if (!confirm("Delete this monitoring record?")) {
        return;
      }

      this.deletingRecords = { ...this.deletingRecords, [recordId]: true };

      try {
        await axios.delete(route("api.research.records.destroy", recordId));
        this.resetRecordForm(sampleId);
        router.visit(route("research.experiments.show", this.experiment.id));
      } finally {
        this.deletingRecords = { ...this.deletingRecords, [recordId]: false };
      }
    },
  },
};
</script>

<template>
    <AppLayout :title="experiment.title">
        <template #header>
            <div class="border-b border-slate-200 bg-white px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                            <Link :href="route('research.projects.show', experiment.study?.project?.id)" class="hover:text-slate-700">
                                {{ experiment.study?.project?.code || "Project" }}
                            </Link>
                            <LuChevronRight class="h-4 w-4" />
                            <Link :href="route('research.studies.show', experiment.study?.id)" class="hover:text-slate-700">
                                {{ experiment.study?.code || "Study" }}
                            </Link>
                            <LuChevronRight class="h-4 w-4" />
                            <span class="text-slate-900">{{ experiment.code }}</span>
                        </div>
                        <h1 class="text-xl font-semibold text-slate-900 sm:text-2xl">{{ experiment.title }}</h1>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a v-if="canExport" :href="route('api.research.experiments.export.samples', experiment.id)"
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                            <LuDownload class="h-4 w-4" />
                            Export CSV
                        </a>
                        <button v-if="canManageExperiment" @click="editingExperiment = !editingExperiment"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                            <LuPencil class="h-4 w-4" />
                            {{ editingExperiment ? 'Cancel' : 'Edit' }}
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <div class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8 space-y-6">
            <!-- Experiment Overview -->
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="border-b border-slate-100 px-6 py-4">
                    <p class="text-xs font-mono text-slate-500">{{ experiment.code }}</p>
                    <h2 class="text-lg font-semibold text-slate-900">Experiment Details</h2>
                </div>
                <div class="p-6">
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Location</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ experiment.geographic_location || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Season</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ experiment.season || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Commodity</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ experiment.commodity || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Sample Type</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ experiment.sample_type || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Generation</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ experiment.generation || experiment.filial_generation || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Plot/Field</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">
                                {{ experiment.plot_number || '—' }} / {{ experiment.field_number || '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Replications</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ experiment.replication_number || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Planned Plants</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ experiment.planned_plant_count || '—' }}</dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Cross Combination</dt>
                            <dd class="mt-1 text-sm font-mono text-slate-700 bg-slate-50 rounded px-3 py-2">
                                {{ experiment.cross_combination || 'Not recorded' }}
                            </dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Parental Background</dt>
                            <dd class="mt-1 text-sm text-slate-700">
                                {{ experiment.parental_background || 'Not recorded' }}
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sample Management -->
            <div v-if="canManageSamples" class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-900">Register New Sample</h2>
                    <p class="text-sm text-slate-500">Add field or laboratory samples to this experiment</p>
                </div>
                <div class="p-6">
                    <!-- Sample form fields in grid layout -->
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Accession Name <span class="text-red-500">*</span></label>
                            <input v-model="newSampleForm.accession_name"
                                class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="e.g., NSIC Rc222" />
                            <p v-if="fieldError(sampleCreateErrors, 'accession_name')" class="mt-1 text-xs text-red-600">
                                {{ fieldError(sampleCreateErrors, 'accession_name') }}
                            </p>
                        </div>
                        <!-- Additional fields following same pattern -->
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="button" :disabled="creatingSample" @click="createSample"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50">
                            <LuLoader2 v-if="creatingSample" class="mr-2 h-4 w-4 animate-spin" />
                            <LuPlus v-else class="mr-2 h-4 w-4" />
                            {{ creatingSample ? 'Creating...' : 'Create Sample' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Samples List -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Tracked Samples</h2>
                        <p class="text-sm text-slate-500">{{ (experiment.samples || []).length }} samples registered</p>
                    </div>
                </div>

                <div v-if="!(experiment.samples || []).length" class="rounded-xl bg-white py-12 text-center shadow-sm ring-1 ring-slate-200">
                    <LuDna class="mx-auto h-10 w-10 text-slate-300" />
                    <h3 class="mt-3 text-sm font-medium text-slate-900">No samples yet</h3>
                    <p class="mt-1 text-sm text-slate-500">Register samples to begin monitoring and data collection.</p>
                </div>

                <div v-for="sample in experiment.samples || []" :key="sample.id"
                    class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
                    <!-- Sample card content -->
                    <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between bg-slate-50/50">
                        <div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-mono text-slate-500">{{ sample.uid }}</span>
                                <span v-if="sample.is_priority"
                                    class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                    Priority
                                </span>
                            </div>
                            <h3 class="text-base font-semibold text-slate-900">{{ sample.accession_name }}</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">
                                {{ (sample.monitoring_records || []).length }} records
                            </span>
                        </div>
                    </div>
                    <!-- Sample editing and monitoring records sections -->
                </div>
            </div>
        </div>
    </AppLayout>
</template>