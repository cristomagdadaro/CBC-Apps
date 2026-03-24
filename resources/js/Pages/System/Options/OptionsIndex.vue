<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Options from "@/Modules/domain/Options";

export default {
  name: "OptionsIndex",
  mixins: [ApiMixin],
  data() {
    return {
      optionsModel: Options,
      workflowToggles: {
        event_workflow_enabled: true,
        participant_workflow_enabled: true,
        participant_verification_enabled: true,
      },
      workflowToggleLoading: false,
      workflowToggleSaving: false,
    };
  },
  mounted() {
    this.loadWorkflowToggles();
  },

  methods: {
    async loadWorkflowToggles() {
      this.workflowToggleLoading = true;
      try {
        const response = await this.fetchGetApi("api.options.workflow-toggles");
        const data = response?.data || {};
        this.workflowToggles = {
          event_workflow_enabled: data.event_workflow_enabled !== false,
          participant_workflow_enabled: data.participant_workflow_enabled !== false,
          participant_verification_enabled:
            data.participant_verification_enabled !== false,
        };
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
          participant_workflow_enabled: !!this.workflowToggles
            .participant_workflow_enabled,
          participant_verification_enabled: !!this.workflowToggles
            .participant_verification_enabled,
        };

        const response = await this.fetchPutApi(
          "api.options.workflow-toggles.update",
          null,
          payload
        );
        const data = response?.data?.data || payload;
        this.workflowToggles = {
          event_workflow_enabled: data.event_workflow_enabled !== false,
          participant_workflow_enabled: data.participant_workflow_enabled !== false,
          participant_verification_enabled:
            data.participant_verification_enabled !== false,
        };
      } catch (error) {
        // ApiService handles error notification
      } finally {
        this.workflowToggleSaving = false;
      }
    },
  },
};
</script>

<template>
  <app-layout title="System Options">
    <template #header>
      <ActionHeaderLayout
        title="System Options"
        subtitle="Manage system-wide settings and configuration options"
        route-link="system.options.index"
      >
        <a :href="route('system.options.create')">
          <add-icon class="h-auto w-5 text-white dark:text-gray-800 dark:bg-gray-200" />
        </a>
      </ActionHeaderLayout>
    </template>
    <div class="min-h-screen py-5">
      <div class="max-w-[90vw] mx-auto sm:px-6 lg:px-8 flex flex-col gap-5">
        <!-- Workflow Toggles Card -->
        <div
          class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
        >
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
                    Enable or disable event, participant, and verification workflows
                    across the system.
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
                  'border-indigo-500 dark:border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20':
                    workflowToggles.event_workflow_enabled,
                }"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-10 h-10 rounded-lg flex items-center justify-center transition-colors"
                    :class="
                      workflowToggles.event_workflow_enabled
                        ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400'
                        : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400'
                    "
                  >
                    <LuCheckCircle
                      v-if="workflowToggles.event_workflow_enabled"
                      class="w-5 h-5"
                    />
                    <LuXCircle v-else class="w-5 h-5" />
                  </div>
                  <div>
                    <span
                      class="block text-sm font-semibold text-gray-900 dark:text-white"
                      >Event Workflow</span
                    >
                    <span class="text-xs text-gray-500 dark:text-gray-400"
                      >Manage event processing</span
                    >
                  </div>
                </div>
                <input
                  type="checkbox"
                  v-model="workflowToggles.event_workflow_enabled"
                  class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0 cursor-pointer"
                />
              </label>

              <label
                class="group relative flex items-center justify-between p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700 transition-all duration-200 cursor-pointer bg-gray-50 dark:bg-gray-800/50 hover:bg-white dark:hover:bg-gray-800"
                :class="{
                  'border-indigo-500 dark:border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20':
                    workflowToggles.participant_workflow_enabled,
                }"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-10 h-10 rounded-lg flex items-center justify-center transition-colors"
                    :class="
                      workflowToggles.participant_workflow_enabled
                        ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400'
                        : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400'
                    "
                  >
                    <LuCheckCircle
                      v-if="workflowToggles.participant_workflow_enabled"
                      class="w-5 h-5"
                    />
                    <LuXCircle v-else class="w-5 h-5" />
                  </div>
                  <div>
                    <span
                      class="block text-sm font-semibold text-gray-900 dark:text-white"
                      >Participant Workflow</span
                    >
                    <span class="text-xs text-gray-500 dark:text-gray-400"
                      >Handle participant logic</span
                    >
                  </div>
                </div>
                <input
                  type="checkbox"
                  v-model="workflowToggles.participant_workflow_enabled"
                  class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0 cursor-pointer"
                />
              </label>

              <label
                class="group relative flex items-center justify-between p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700 transition-all duration-200 cursor-pointer bg-gray-50 dark:bg-gray-800/50 hover:bg-white dark:hover:bg-gray-800"
                :class="{
                  'border-indigo-500 dark:border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20':
                    workflowToggles.participant_verification_enabled,
                }"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-10 h-10 rounded-lg flex items-center justify-center transition-colors"
                    :class="
                      workflowToggles.participant_verification_enabled
                        ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400'
                        : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400'
                    "
                  >
                    <LuCheckCircle
                      v-if="workflowToggles.participant_verification_enabled"
                      class="w-5 h-5"
                    />
                    <LuXCircle v-else class="w-5 h-5" />
                  </div>
                  <div>
                    <span
                      class="block text-sm font-semibold text-gray-900 dark:text-white"
                      >Verification</span
                    >
                    <span class="text-xs text-gray-500 dark:text-gray-400"
                      >Enable verification steps</span
                    >
                  </div>
                </div>
                <input
                  type="checkbox"
                  v-model="workflowToggles.participant_verification_enabled"
                  class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0 cursor-pointer"
                />
              </label>
            </div>
          </div>
        </div>
        <CRCMDatatable :base-model="optionsModel" :can-view="true" :can-create="true" :can-update="true" :can-delete="true" />
      </div>
    </div>
  </app-layout>
</template>
