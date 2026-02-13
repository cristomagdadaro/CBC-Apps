<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm">
      <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">System Options</h1>
            <p class="mt-2 text-sm text-gray-600">Manage system-wide settings and configuration options</p>
          </div>
          <Link
            :href="route('system.options.create')"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
          >
            <span>+ New Option</span>
          </Link>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white border-b border-gray-200 py-4">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search options..."
            class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
          />
          <select
            v-model="filters.group"
            class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
          >
            <option value="">All Groups</option>
            <option value="system">System</option>
            <option value="email">Email</option>
            <option value="inventory">Inventory</option>
            <option value="forms">Forms</option>
            <option value="rental">Rental</option>
            <option value="requests">Requests</option>
            <option value="locations">Locations</option>
            <option value="reports">Reports</option>
          </select>
          <select
            v-model="filters.type"
            class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
          >
            <option value="">All Types</option>
            <option value="text">Text</option>
            <option value="select">Select</option>
            <option value="textarea">Textarea</option>
            <option value="checkbox">Checkbox</option>
            <option value="number">Number</option>
            <option value="boolean">Boolean</option>
            <option value="json">JSON</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <div class="space-y-4">
        <div v-for="i in 5" :key="i" class="h-16 bg-gray-200 rounded-lg animate-pulse"></div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="paginatedOptions.length === 0" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <div class="text-center">
        <p class="text-gray-500">No options found. Create one to get started.</p>
      </div>
    </div>

    <!-- Table -->
    <div v-else class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700">
                Key
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700">
                Label
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700">
                Group
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700">
                Type
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700">
                Value Preview
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="option in paginatedOptions" :key="option.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm font-mono text-gray-900">{{ option.key }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ option.label }}</td>
              <td class="px-6 py-4 text-sm">
                <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800">
                  {{ option.group }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm">
                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-800">
                  {{ option.type }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">
                <span v-if="option.type === 'json'" class="text-xs">{{ getValuePreview(option.value, 50) }}</span>
                <span v-else>{{ getValuePreview(option.value, 50) }}</span>
              </td>
              <td class="px-6 py-4 text-sm">
                <div class="flex gap-2">
                  <Link
                    :href="route('system.options.show', option.id)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Edit
                  </Link>
                  <button
                    @click="openDeleteModal(option)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="mt-4 flex items-center justify-between">
        <div class="text-sm text-gray-600">
          Showing {{ startIndex + 1 }} to {{ Math.min(endIndex, filteredOptions.length) }} of {{ filteredOptions.length }} options
        </div>
        <div class="flex gap-2">
          <button
            @click="currentPage--"
            :disabled="currentPage === 1"
            class="rounded-lg border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50 disabled:opacity-50"
          >
            Previous
          </button>
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="currentPage = page"
            :class="[
              'rounded-lg px-4 py-2 text-sm',
              currentPage === page
                ? 'bg-blue-600 text-white'
                : 'border border-gray-300 hover:bg-gray-50'
            ]"
          >
            {{ page }}
          </button>
          <button
            @click="currentPage++"
            :disabled="currentPage === totalPages"
            class="rounded-lg border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50 disabled:opacity-50"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <Teleport to="body">
      <div
        v-if="deleteModal.isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
      >
        <div class="rounded-lg bg-white p-6 shadow-xl max-w-md w-full mx-4">
          <h3 class="text-lg font-semibold text-gray-900">Delete Option</h3>
          <p class="mt-2 text-sm text-gray-600">
            Are you sure you want to delete <strong>{{ deleteModal.option?.key }}</strong>? This action cannot be undone.
          </p>
          <div class="mt-6 flex gap-3 justify-end">
            <button
              @click="deleteModal.isOpen = false"
              class="rounded-lg border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              @click="deleteOption()"
              :disabled="deleteModal.isDeleting"
              class="rounded-lg bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700 disabled:opacity-50"
            >
              {{ deleteModal.isDeleting ? 'Deleting...' : 'Delete' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import axios from 'axios'
import { useNotifier } from '@/Modules/composables/useNotifier'

const { success, error: notifyError } = useNotifier()

const props = defineProps({
  options: {
    type: Array,
    default: () => [],
  },
})

const loading = ref(false)
const currentPage = ref(1)
const itemsPerPage = 10
const filters = ref({
  search: '',
  group: '',
  type: '',
})

const deleteModal = ref({
  isOpen: false,
  option: null,
  isDeleting: false,
})

const filteredOptions = computed(() => {
  return props.options.filter(option => {
    const matchesSearch = option.key.toLowerCase().includes(filters.value.search.toLowerCase()) ||
      option.label.toLowerCase().includes(filters.value.search.toLowerCase()) ||
      option.description?.toLowerCase().includes(filters.value.search.toLowerCase())
    
    const matchesGroup = !filters.value.group || option.group === filters.value.group
    const matchesType = !filters.value.type || option.type === filters.value.type
    
    return matchesSearch && matchesGroup && matchesType
  })
})

const totalPages = computed(() => {
  return Math.ceil(filteredOptions.value.length / itemsPerPage)
})

const startIndex = computed(() => {
  return (currentPage.value - 1) * itemsPerPage
})

const endIndex = computed(() => {
  return startIndex.value + itemsPerPage
})

const paginatedOptions = computed(() => {
  return filteredOptions.value.slice(startIndex.value, endIndex.value)
})

const visiblePages = computed(() => {
  const pages = []
  const maxPagesToShow = 5
  let start = Math.max(1, currentPage.value - Math.floor(maxPagesToShow / 2))
  let end = Math.min(totalPages.value, start + maxPagesToShow - 1)
  
  if (end - start < maxPagesToShow - 1) {
    start = Math.max(1, end - maxPagesToShow + 1)
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

const getValuePreview = (value, maxLength = 50) => {
  if (!value) return '—'
  const str = typeof value === 'string' ? value : JSON.stringify(value)
  return str.length > maxLength ? str.substring(0, maxLength) + '...' : str
}

const openDeleteModal = (option) => {
  deleteModal.value.option = option
  deleteModal.value.isOpen = true
}

const deleteOption = async () => {
  if (!deleteModal.value.option) return
  
  deleteModal.value.isDeleting = true
  try {
    await axios.delete(route('api.options.destroy', deleteModal.value.option.id))
    deleteModal.value.isOpen = false
    success('Option deleted successfully.')
    // Refresh the page
    location.reload()
  } catch (error) {
    console.error('Error deleting option:', error)
    notifyError('Failed to delete option.')
  } finally {
    deleteModal.value.isDeleting = false
  }
}
</script>
