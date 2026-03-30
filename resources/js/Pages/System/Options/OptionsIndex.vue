<script>
import TabNavigation from "@/Components/TabNavigation.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Options from "@/Modules/domain/Options";

export default {
  name: "OptionsIndex",
  components: {
    TabNavigation,
  },
  mixins: [ApiMixin],
  data() {
    return {
      activeTab: "controls",
      optionsModel: Options,
      workflowToggles: {
        event_workflow_enabled: true,
        participant_workflow_enabled: true,
        participant_verification_enabled: true,
      },
      workflowToggleLoading: false,
      workflowToggleSaving: false,
      deploymentAccessValues: {},
      deploymentAccessSections: [],
      deploymentAccessLoading: false,
      deploymentAccessSaving: false,
    };
  },
  mounted() {
    this.loadInitialState();
  },
  computed: {
    deploymentAccessMeta() {
      return this.$page.props.deployment_access || {};
    },
    optionTabs() {
      return [
        {
          key: "controls",
          label: "Controls",
          icon: "LuSettings2",
        },
        {
          key: "options",
          label: "All Options",
          icon: "LuTableProperties",
        },
      ];
    },
  },
  methods: {
    extractApiPayload(response, fallback = {}) {
      const payload = response?.data?.data ?? response?.data ?? response;

      return payload && typeof payload === "object" ? payload : fallback;
    },
    syncWorkflowToggles(data = {}) {
      this.workflowToggles = {
        event_workflow_enabled: data.event_workflow_enabled !== false,
        participant_workflow_enabled: data.participant_workflow_enabled !== false,
        participant_verification_enabled:
          data.participant_verification_enabled !== false,
      };
    },
    async loadInitialState() {
      await Promise.allSettled([
        this.loadWorkflowToggles(),
        this.loadDeploymentAccess(),
      ]);
    },
    isModuleProtected(item) {
      return item?.allows_deactivation === false;
    },
    moduleMode(item) {
      return (
        this.deploymentAccessValues[item?.module]?.mode || item?.mode || "active"
      );
    },
    moduleStatusBadge(item) {
      const mode = this.moduleMode(item);

      if (mode === "maintenance") {
        return {
          label: "Maintenance",
          className:
            "bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300",
        };
      }

      if (mode === "deactivated") {
        return {
          label: "Deactivated",
          className:
            "bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300",
        };
      }

      return {
        label: "Active",
        className:
          "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300",
      };
    },
    moduleStatusBanner(item) {
      const mode = this.moduleMode(item);

      if (mode === "maintenance") {
        return {
          className:
            "border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-200",
          text: "Maintenance mode keeps this module visible but blocks create, update, and delete requests.",
        };
      }

      if (mode === "deactivated") {
        return {
          className:
            "border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-200",
          text: "This module is fully unavailable and is hidden from the app layout where applicable.",
        };
      }

      return null;
    },
    applyDeploymentAccessPayload(payload = {}) {
      const sections = Array.isArray(payload.sections) ? payload.sections : [];
      const modules = payload.modules || {};

      this.deploymentAccessSections = sections;
      this.deploymentAccessValues = Object.fromEntries(
        Object.entries(modules).map(([module, settings]) => [
          module,
          {
            access: settings?.access || "both",
            mode: settings?.mode || "active",
          },
        ])
      );
    },
    async loadWorkflowToggles() {
      this.workflowToggleLoading = true;
      try {
        const response = await this.fetchGetApi("api.options.workflow-toggles");
        this.syncWorkflowToggles(this.extractApiPayload(response));
      } catch (error) {
        // ApiService handles error notification
      } finally {
        this.workflowToggleLoading = false;
      }
    },
    async saveWorkflowToggles() {
      this.workflowToggleSaving = true;
      try {
        const payload = {
          event_workflow_enabled: !!this.workflowToggles.event_workflow_enabled,
          participant_workflow_enabled:
            !!this.workflowToggles.participant_workflow_enabled,
          participant_verification_enabled:
            !!this.workflowToggles.participant_verification_enabled,
        };

        const response = await this.fetchPutApi(
          "api.options.workflow-toggles.update",
          null,
          payload
        );

        this.syncWorkflowToggles(this.extractApiPayload(response, payload));
      } catch (error) {
        // ApiService handles error notification
      } finally {
        this.workflowToggleSaving = false;
      }
    },
    async loadDeploymentAccess() {
      this.deploymentAccessLoading = true;
      try {
        const response = await this.fetchGetApi("api.options.deployment-access");
        this.applyDeploymentAccessPayload(this.extractApiPayload(response));
      } catch (error) {
        // ApiService handles error notification
      } finally {
        this.deploymentAccessLoading = false;
      }
    },
    async saveDeploymentAccess() {
      this.deploymentAccessSaving = true;
      try {
        const response = await this.fetchPutApi(
          "api.options.deployment-access.update",
          null,
          {
            modules: this.deploymentAccessValues,
          }
        );

        this.applyDeploymentAccessPayload(this.extractApiPayload(response));
      } catch (error) {
        // ApiService handles error notification
      } finally {
        this.deploymentAccessSaving = false;
      }
    },
  },
};
</script>

<template>
  <app-layout title="Options Module">
    <template #header>
      <ActionHeaderLayout
        title="Options Module"
        subtitle="Manage module controls, workflow toggles, and configuration options"
        route-link="system.options.index"
      >
        <Link :href="route('system.options.create')">
          <add-icon
            class="h-auto w-5 text-white dark:text-gray-800 dark:bg-gray-200"
          />
        </Link>
      </ActionHeaderLayout>
    </template>

    <div class="min-h-screen py-5">
      <div class="max-w-[90vw] mx-auto sm:px-6 lg:px-8">
        <TabNavigation v-model="activeTab" :tabs="optionTabs">
          <template #default="{ activeKey }">
            <div
              v-if="activeKey === 'controls'"
              class="rounded-b-xl border-x border-b border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800 space-y-5"
            >
              <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6">
                  <div class="flex items-start justify-between gap-4 mb-6">
                    <div class="flex items-start gap-3">
                      <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg mt-1">
                        <LuLayoutGrid class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                      </div>
                      <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                          Form Workflow Toggles
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                          Enable or disable event, participant, and verification workflows across the system.
                        </p>
                      </div>
                    </div>
                    <button
                      type="button"
                      @click="saveWorkflowToggles"
                      :disabled="workflowToggleLoading || workflowToggleSaving"
                      class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 dark:disabled:bg-gray-700 disabled:cursor-not-allowed text-white rounded-lg transition-all duration-200 shadow-sm"
                    >
                      <LuLoader2 v-if="workflowToggleSaving" class="w-4 h-4 animate-spin" />
                      <LuSave v-else class="w-4 h-4" />
                      <span>{{ workflowToggleSaving ? "Saving..." : "Save Changes" }}</span>
                    </button>
                  </div>

                  <div
                    v-if="workflowToggleLoading"
                    class="flex items-center justify-center py-8 text-gray-500 dark:text-gray-400"
                  >
                    <LuLoader2 class="w-5 h-5 animate-spin mr-2" />
                    <span>Loading workflow settings...</span>
                  </div>

                  <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label
                      class="group relative flex items-center justify-between p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700 transition-all duration-200 cursor-pointer bg-gray-50 dark:bg-gray-800/50 hover:bg-white dark:hover:bg-gray-800"
                      :class="{
                        'border-indigo-500 dark:border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20': workflowToggles.event_workflow_enabled,
                      }"
                    >
                      <div class="flex items-center gap-3">
                        <div
                          class="w-10 h-10 rounded-lg flex items-center justify-center transition-colors"
                          :class="workflowToggles.event_workflow_enabled
                            ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400'
                            : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400'"
                        >
                          <LuCheckCircle v-if="workflowToggles.event_workflow_enabled" class="w-5 h-5" />
                          <LuXCircle v-else class="w-5 h-5" />
                        </div>
                        <div>
                          <span class="block text-sm font-semibold text-gray-900 dark:text-white">
                            Event Workflow
                          </span>
                          <span class="text-xs text-gray-500 dark:text-gray-400">
                            Manage event processing
                          </span>
                        </div>
                      </div>
                      <input
                        v-model="workflowToggles.event_workflow_enabled"
                        type="checkbox"
                        class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0 cursor-pointer"
                      />
                    </label>

                    <label
                      class="group relative flex items-center justify-between p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700 transition-all duration-200 cursor-pointer bg-gray-50 dark:bg-gray-800/50 hover:bg-white dark:hover:bg-gray-800"
                      :class="{
                        'border-indigo-500 dark:border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20': workflowToggles.participant_workflow_enabled,
                      }"
                    >
                      <div class="flex items-center gap-3">
                        <div
                          class="w-10 h-10 rounded-lg flex items-center justify-center transition-colors"
                          :class="workflowToggles.participant_workflow_enabled
                            ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400'
                            : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400'"
                        >
                          <LuCheckCircle v-if="workflowToggles.participant_workflow_enabled" class="w-5 h-5" />
                          <LuXCircle v-else class="w-5 h-5" />
                        </div>
                        <div>
                          <span class="block text-sm font-semibold text-gray-900 dark:text-white">
                            Participant Workflow
                          </span>
                          <span class="text-xs text-gray-500 dark:text-gray-400">
                            Handle participant logic
                          </span>
                        </div>
                      </div>
                      <input
                        v-model="workflowToggles.participant_workflow_enabled"
                        type="checkbox"
                        class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0 cursor-pointer"
                      />
                    </label>

                    <label
                      class="group relative flex items-center justify-between p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700 transition-all duration-200 cursor-pointer bg-gray-50 dark:bg-gray-800/50 hover:bg-white dark:hover:bg-gray-800"
                      :class="{
                        'border-indigo-500 dark:border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20': workflowToggles.participant_verification_enabled,
                      }"
                    >
                      <div class="flex items-center gap-3">
                        <div
                          class="w-10 h-10 rounded-lg flex items-center justify-center transition-colors"
                          :class="workflowToggles.participant_verification_enabled
                            ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400'
                            : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400'"
                        >
                          <LuCheckCircle
                            v-if="workflowToggles.participant_verification_enabled"
                            class="w-5 h-5"
                          />
                          <LuXCircle v-else class="w-5 h-5" />
                        </div>
                        <div>
                          <span class="block text-sm font-semibold text-gray-900 dark:text-white">
                            Verification
                          </span>
                          <span class="text-xs text-gray-500 dark:text-gray-400">
                            Enable verification steps
                          </span>
                        </div>
                      </div>
                      <input
                        v-model="workflowToggles.participant_verification_enabled"
                        type="checkbox"
                        class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0 cursor-pointer"
                      />
                    </label>
                  </div>
                </div>
              </div>

              <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6">
                  <div class="flex items-start justify-between gap-4 mb-6">
                    <div class="flex items-start gap-3">
                      <div class="p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg mt-1">
                        <LuLayoutGrid class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                      </div>
                      <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                          Module Access Controls
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                          Configure deployment visibility and runtime mode for each module. Deployment access applies to the module's web pages and APIs together.
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
                          <span class="px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-700">
                            Local: {{ deploymentAccessMeta.local_url }}
                          </span>
                          <span class="px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-700">
                            Internet: {{ deploymentAccessMeta.internet_url }}
                          </span>
                        </div>
                      </div>
                    </div>
                    <button
                      type="button"
                      @click="saveDeploymentAccess"
                      :disabled="deploymentAccessLoading || deploymentAccessSaving"
                      class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-300 dark:disabled:bg-gray-700 disabled:cursor-not-allowed text-white rounded-lg transition-all duration-200 shadow-sm"
                    >
                      <LuLoader2 v-if="deploymentAccessSaving" class="w-4 h-4 animate-spin" />
                      <LuSave v-else class="w-4 h-4" />
                      <span>{{ deploymentAccessSaving ? "Saving..." : "Save Module Rules" }}</span>
                    </button>
                  </div>

                  <div
                    v-if="deploymentAccessLoading"
                    class="flex items-center justify-center py-8 text-gray-500 dark:text-gray-400"
                  >
                    <LuLoader2 class="w-5 h-5 animate-spin mr-2" />
                    <span>Loading module controls...</span>
                  </div>

                  <div v-else class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                    <section
                      v-for="section in deploymentAccessSections"
                      :key="section.key"
                      class="rounded-xl bg-gray-50 dark:bg-gray-900/30 p-4"
                    >
                      <div class="mb-4">
                        <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-900 dark:text-white">
                          {{ section.label }}
                        </h4>
                      </div>

                      <div class="space-y-4">
                        <div
                          v-for="item in section.items"
                          :key="item.module"
                          class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4"
                        >
                          <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="pr-0 md:pr-4">
                              <div class="flex flex-wrap items-center gap-2">
                                <h5 class="text-sm font-semibold text-gray-900 dark:text-white">
                                  {{ item.label }}
                                </h5>
                                <span
                                  class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                  :class="moduleStatusBadge(item).className"
                                >
                                  {{ moduleStatusBadge(item).label }}
                                </span>
                                <span
                                  v-if="isModuleProtected(item)"
                                  class="inline-flex items-center rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-900/30 dark:text-sky-300"
                                >
                                  Safeguarded
                                </span>
                              </div>
                              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ item.description }}
                              </p>
                            </div>

                            <div class="grid w-full shrink-0 gap-3 md:w-[22rem] md:grid-cols-2">
                              <div>
                                <label
                                  :for="`${item.module}-access`"
                                  class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                                >
                                  Deployment Access
                                </label>
                                <select
                                  :id="`${item.module}-access`"
                                  v-model="deploymentAccessValues[item.module].access"
                                  class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-4 py-2 text-sm text-gray-900 dark:text-white focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                >
                                  <option
                                    v-for="option in item.access_options"
                                    :key="`${item.module}-access-${option.value}`"
                                    :value="option.value"
                                  >
                                    {{ option.label }}
                                  </option>
                                </select>
                              </div>

                              <div>
                                <label
                                  :for="`${item.module}-mode`"
                                  class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                                >
                                  Module Mode
                                </label>
                                <select
                                  :id="`${item.module}-mode`"
                                  v-model="deploymentAccessValues[item.module].mode"
                                  class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-4 py-2 text-sm text-gray-900 dark:text-white focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                >
                                  <option
                                    v-for="option in item.mode_options"
                                    :key="`${item.module}-mode-${option.value}`"
                                    :value="option.value"
                                    :disabled="isModuleProtected(item) && option.value === 'deactivated'"
                                  >
                                    {{ isModuleProtected(item) && option.value === "deactivated" ? `${option.label} (locked)` : option.label }}
                                  </option>
                                </select>
                              </div>
                              <p
                                  v-if="isModuleProtected(item)"
                                  class="text-xs text-sky-600 dark:text-sky-300 col-span-full bg-sky-50 dark:bg-sky-900/20 rounded-md p-2"
                                >
                                  This module cannot be deactivated from the admin UI to avoid locking out system settings.
                                </p>
                            </div>
                          </div>

                          <div
                            v-if="moduleStatusBanner(item)"
                            class="mt-4 rounded-lg border px-3 py-2 text-xs font-medium"
                            :class="moduleStatusBanner(item).className"
                          >
                            {{ moduleStatusBanner(item).text }}
                          </div>
                        </div>
                      </div>
                    </section>
                  </div>
                </div>
              </div>
            </div>

            <div
              v-else
              class="rounded-b-xl border-x border-b border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 p-3"
            >
              <CRCMDatatable
                :base-model="optionsModel"
                :can-view="true"
                :can-create="true"
                :can-update="true"
                :can-delete="false"
              />
            </div>
          </template>
        </TabNavigation>
      </div>
    </div>
  </app-layout>
</template>
