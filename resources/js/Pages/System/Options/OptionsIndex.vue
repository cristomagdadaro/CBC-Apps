<script>
import axios from 'axios'
import { useNotifier } from '@/Modules/composables/useNotifier'
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
      success: null,
      notifyError: null,
    }
  },
  created() {
    const notifier = useNotifier()
    this.success = notifier.success
    this.notifyError = notifier.error
  },
  beforeMount() {
    this.model = new Options();
    this.setFormAction('get');
  },
  mounted() {
    this.searchOptions();
  },

  methods: {
    async handleDeleteRecord(row) {
      const id = row?.identifier?.()?.id ?? row?.id
      if (!id) return
      try {
        await axios.delete(route('api.options.destroy', id))
        this.success('Option deleted successfully.')
        await this.searchOptions()
      } catch (err) {
        console.error('Error deleting option:', err)
        this.notifyError('Failed to delete option.')
      }
    },
    async searchOptions() {
      this.optionsFromApi = await this.fetchData();
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
      <a :href="route('system.options.create')"  target="_blank">
        <add-icon class="h-auto w-5 text-AA dark:text-gray-800 dark:bg-gray-200" />
      </a>
    </ActionHeaderLayout>
  </template> 
  <div class="min-h-screen py-5">
    <div class="max-w-[90vw] mx-auto sm:px-6 lg:px-8">
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
      <div class="bg-white dark:bg-gray-800 overflow-hidden sm:rounded-lg mt-4">
        <data-table
          v-if="optionsFromApi && optionsFromApi.total > 0 && !processing"
          :api-response="optionsFromApi"
          :processing="processing"
          :model="model.constructor"
          @delete-record="handleDeleteRecord"
        />
        <!-- Show "Searching" when processing -->
        <div v-else-if="processing" class="text-center py-3 border border-gray-300 rounded-lg">
          Searching...
        </div>
        <!-- Show "No options" when search was performed but no results -->
        <div v-else-if="optionsFromApi && optionsFromApi.total === 0 && form.search" class="text-center py-3 border border-gray-300 rounded-lg">
          No options found. Try using some filters.
        </div>
        <!-- Show "No options available" when nothing was returned and no search was performed -->
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