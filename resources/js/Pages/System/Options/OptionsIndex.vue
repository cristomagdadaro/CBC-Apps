<script>
import ApiMixin from '@/Modules/mixins/ApiMixin'
import Options from '@/Modules/domain/Options'

export default {
  name: 'OptionsIndex',
  mixins: [ApiMixin],
  props: {
    options: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      optionsFromApi: null,
      workflowToggles: {
        event_workflow_enabled: true,
        participant_workflow_enabled: true,
        participant_verification_enabled: true,
      },
      workflowToggleLoading: false,
      workflowToggleSaving: false,
    }
  },
  created() {
  },
  beforeMount() {
    this.model = new Options();
    this.setFormAction('get');
  },
  mounted() {
    this.loadWorkflowToggles();
    this.searchOptions();
  },
  computed: {
    groupedOptions() {
      const rows = Array.isArray(this.optionsFromApi?.data) ? this.optionsFromApi.data : []

      return rows.reduce((groups, option) => {
        const groupName = option?.group || 'uncategorized'

        if (!groups[groupName]) {
          groups[groupName] = []
        }

        groups[groupName].push(option)
        return groups
      }, {})
    },
    groupedEntries() {
      return Object.entries(this.groupedOptions)
        .sort(([groupA], [groupB]) => groupA.localeCompare(groupB))
    },
  },

  methods: {
    async loadWorkflowToggles() {
      this.workflowToggleLoading = true
      try {
        const response = await this.fetchGetApi('api.options.workflow-toggles')
        const data = response?.data || {}
        this.workflowToggles = {
          event_workflow_enabled: data.event_workflow_enabled !== false,
          participant_workflow_enabled: data.participant_workflow_enabled !== false,
          participant_verification_enabled: data.participant_verification_enabled !== false,
        }
      } catch (error) {
        // ApiService handles error notification
      } finally {
        this.workflowToggleLoading = false
      }
    },
    async saveWorkflowToggles() {
      this.workflowToggleSaving = true
      try {
        const payload = {
          event_workflow_enabled: !!this.workflowToggles.event_workflow_enabled,
          participant_workflow_enabled: !!this.workflowToggles.participant_workflow_enabled,
          participant_verification_enabled: !!this.workflowToggles.participant_verification_enabled,
        }

        const response = await this.fetchPutApi('api.options.workflow-toggles.update', null, payload)
        const data = response?.data?.data || payload
        this.workflowToggles = {
          event_workflow_enabled: data.event_workflow_enabled !== false,
          participant_workflow_enabled: data.participant_workflow_enabled !== false,
          participant_verification_enabled: data.participant_verification_enabled !== false,
        }
      } catch (error) {
        // ApiService handles error notification
      } finally {
        this.workflowToggleSaving = false
      }
    },
    async handleDeleteRecord(row) {
      const id = row?.identifier?.()?.id ?? row?.id
      if (!id) return
      try {
        await this.fetchDeleteApi('api.options.destroy', id)
        await this.searchOptions()
      } catch (err) {
        // ApiService handles error notification
      }
    },
    async searchOptions() {
      this.optionsFromApi = await this.fetchData();
    },
    formatOptionValue(option) {
      if (option?.type === 'boolean' || option?.type === 'checkbox') {
        const normalized = String(option?.value ?? '').toLowerCase()
        return ['1', 'true', 'yes', 'on'].includes(normalized) ? 'Enabled' : 'Disabled'
      }

      if (option?.type === 'json' || option?.type === 'select') {
        try {
          const parsed = typeof option?.value === 'string'
            ? JSON.parse(option.value)
            : option?.value
          return JSON.stringify(parsed, null, 2)
        } catch (error) {
          return option?.value || '-'
        }
      }

      return option?.value ?? '-'
    },
  },
  watch: {
    'form.search': {
      handler(newVal, oldVal) {
        if (!newVal) {
          this.form.filter = null
          this.form.is_exact = null
        }
      },
      deep: true,
    }
  },
}
</script>


<template>
<app-layout title="System Options">
  <template #header>
    <ActionHeaderLayout title="System Options" subtitle="Manage system-wide settings and configuration options" route-link="system.options.index">
      <a :href="route('system.options.create')">
        <add-icon class="h-auto w-5 text-white dark:text-gray-800 dark:bg-gray-200" />
      </a>
    </ActionHeaderLayout>
  </template> 
  <div class="min-h-screen py-5">
    <div class="max-w-[90vw] mx-auto sm:px-6 lg:px-8">
      <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4">
        <div class="flex items-start justify-between gap-4 mb-3">
          <div>
            <h3 class="font-semibold text-gray-900 dark:text-gray-100">Form Workflow Toggles</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300">Enable or disable event, participant, and participant verification logic from frontend to backend.</p>
          </div>
          <button
            type="button"
            class="rounded-lg bg-AB px-4 py-2 text-sm font-medium text-white hover:bg-AB/90 disabled:opacity-60"
            :disabled="workflowToggleLoading || workflowToggleSaving"
            @click="saveWorkflowToggles"
          >
            {{ workflowToggleSaving ? 'Saving...' : 'Save Toggles' }}
          </button>
        </div>

        <div v-if="workflowToggleLoading" class="text-sm text-gray-500 dark:text-gray-400">Loading workflow toggles...</div>
        <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <label class="flex items-center justify-between rounded-md border border-gray-200 dark:border-gray-700 p-3">
            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Event Workflow</span>
            <input type="checkbox" v-model="workflowToggles.event_workflow_enabled" class="rounded border-gray-300 text-AB focus:ring-AB" />
          </label>

          <label class="flex items-center justify-between rounded-md border border-gray-200 dark:border-gray-700 p-3">
            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Participant Workflow</span>
            <input type="checkbox" v-model="workflowToggles.participant_workflow_enabled" class="rounded border-gray-300 text-AB focus:ring-AB" />
          </label>

          <label class="flex items-center justify-between rounded-md border border-gray-200 dark:border-gray-700 p-3">
            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Participant Verification</span>
            <input type="checkbox" v-model="workflowToggles.participant_verification_enabled" class="rounded border-gray-300 text-AB focus:ring-AB" />
          </label>
        </div>
      </div>

      <form v-if="!!form" class="flex gap-2 items-end" @submit.prevent="searchOptions">
        <div class="grid grid-rows-2 w-full">
          <div class="w-full flex gap-2 items-end lg:px-0 px-2">
            <search-by :value="form.filter" :is-exact="form.is_exact" :options="model.constructor.getFilterColumns()" @isExact="form.is_exact = $event" @searchBy="form.filter = $event" />
            <text-input placeholder="Search..." v-model="form.search" />
            <search-btn type="submit" :disabled="processing" class="w-[10rem] text-center">
              <span v-if="!processing">Search</span>
              <span v-else>Searching</span>
            </search-btn>
          </div>
          <div v-if="optionsFromApi" class="flex w-full gap-2 items-center">
            <div id="dtPaginatorContainer" class="flex gap-1 items-center w-full justify-center">
              <!-- First Button -->
              <paginate-btn @click="form.page = 1; searchOptions();" :disabled="form.page === 1">
                First
              </paginate-btn>
              <!-- Previous Button -->
              <paginate-btn @click="form.page = Math.max(1, form.page - 1); searchOptions();" :disabled="form.page === 1">
                <template v-slot:icon>
                  <arrow-left class="h-auto w-6" />
                </template>
                Prev
              </paginate-btn>
              <!-- Current Page Indicator -->
              <div class="text-xs flex flex-col whitespace-nowrap text-center">
                <span class="font-medium mx-1" title="current page and total pages">
                  <span>{{ optionsFromApi?.current_page }}</span> / <span>{{ optionsFromApi?.last_page }}</span>
                </span>
              </div>
              <!-- Next Button -->
              <paginate-btn @click="form.page = Math.min(optionsFromApi?.last_page, form.page + 1); searchOptions();" :disabled="form.page === optionsFromApi?.last_page">
                Next
                <template v-slot:icon>
                  <arrow-right class="h-auto w-6" />
                </template>
              </paginate-btn>
              <!-- Last Button -->
              <paginate-btn @click="form.page = optionsFromApi?.last_page; searchOptions();" :disabled="form.page === optionsFromApi?.last_page">
                Last
              </paginate-btn>
            </div>
          </div>
        </div>
      </form>
      <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mt-4">
        <div class="flex items-start justify-between gap-4 mb-3">
          <div>
            <h3 class="font-semibold text-gray-900 dark:text-gray-100">Options by Group</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300">Grouped view for easier reading while keeping the same option behavior.</p>
          </div>
        </div>

        <div v-if="processing" class="text-center py-3 border border-gray-300 rounded-lg">
          Searching...
        </div>

        <div v-else-if="optionsFromApi && optionsFromApi.total > 0" class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <div
            v-for="([groupName, options]) in groupedEntries"
            :key="groupName"
            class="rounded-md border border-gray-200 dark:border-gray-700 p-3"
          >
            <h4 class="text-sm font-semibold uppercase text-gray-900 dark:text-gray-100 mb-2">{{ groupName }}</h4>

            <div class="space-y-2">
              <div
                v-for="option in options"
                :key="option.id"
                class="rounded-md border border-gray-200 dark:border-gray-700 p-2"
              >
                <div class="flex justify-between gap-2 items-start">
                  <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ option.label }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 break-all">{{ option.key }}</p>
                  </div>
                  <div class="flex items-center gap-2 shrink-0">
                    <Link
                      :href="route('system.options.show', option.id)"
                      class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50"
                    >
                      Edit
                    </Link>
                    <button
                      type="button"
                      class="text-xs px-2 py-1 rounded border border-red-300 text-red-600 hover:bg-red-50"
                      @click="handleDeleteRecord(option)"
                    >
                      Delete
                    </button>
                  </div>
                </div>

                <p v-if="option.description" class="text-xs text-gray-600 dark:text-gray-300 mt-1">{{ option.description }}</p>

                <div class="mt-2">
                  <p class="text-xs font-medium text-gray-700 dark:text-gray-300">Value</p>
                  <pre
                    class="text-xs text-gray-800 dark:text-gray-200 bg-gray-50 dark:bg-gray-900 rounded p-2 whitespace-pre-wrap break-all"
                  >{{ formatOptionValue(option) }}</pre>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-else-if="optionsFromApi && optionsFromApi.total === 0 && form.search" class="text-center py-3 border border-gray-300 rounded-lg">
          No options found. Try using some filters.
        </div>

        <div v-else class="text-center py-3 border border-gray-300 rounded-lg">
          No options available.
        </div>
      </div>
      <div v-if="optionsFromApi && optionsFromApi.data?.length" class="flex w-full gap-2 py-5 items-center">
        <div id="dtPaginatorContainer" class="flex gap-1 items-center w-full justify-center">
          <!-- First Button -->
          <paginate-btn @click="form.page = 1; searchOptions();" :disabled="form.page === 1">
            First
          </paginate-btn>
          <!-- Previous Button -->
          <paginate-btn @click="form.page = Math.max(1, form.page - 1); searchOptions();" :disabled="form.page === 1">
            <template v-slot:icon>
              <arrow-left class="h-auto w-6" />
            </template>
            Prev
          </paginate-btn>
          <!-- Current Page Indicator -->
          <div class="text-xs flex flex-col whitespace-nowrap text-center">
            <span class="font-medium mx-1" title="current page and total pages">
              <span>{{ optionsFromApi?.current_page }}</span> / <span>{{ optionsFromApi?.last_page }}</span>
            </span>
          </div>
          <!-- Next Button -->
          <paginate-btn @click="form.page = Math.min(optionsFromApi?.last_page, form.page + 1); searchOptions();" :disabled="form.page === optionsFromApi?.last_page">
            Next
            <template v-slot:icon>
              <arrow-right class="h-auto w-6" />
            </template>
          </paginate-btn>
          <!-- Last Button -->
          <paginate-btn @click="form.page = optionsFromApi?.last_page; searchOptions();" :disabled="form.page === optionsFromApi?.last_page">
            Last
          </paginate-btn>
        </div>
      </div>
    </div>
  </div>
</app-layout>
</template>